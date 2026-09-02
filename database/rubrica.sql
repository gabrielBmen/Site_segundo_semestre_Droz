-- Rubrica: função, triggers, view e procedure da dashboard.
-- Execute depois de database/droz_robotica.sql.

USE droz_robotica;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS sp_dashboard_indicadores;
DROP VIEW IF EXISTS vw_pedido_itens_analiticos;
DROP FUNCTION IF EXISTS fn_calcular_valor_item;
DROP TRIGGER IF EXISTS trg_produtos_valores_positivos_insert;
DROP TRIGGER IF EXISTS trg_produtos_valores_positivos_update;
DROP TRIGGER IF EXISTS trg_pedido_produto_valores_positivos_update;
DROP TRIGGER IF EXISTS trg_pedidos_valor_positivo_update;

DELIMITER //

CREATE FUNCTION fn_calcular_valor_item(p_quantidade INT, p_preco_unitario DECIMAL(10,2))
RETURNS DECIMAL(12,2)
DETERMINISTIC
NO SQL
BEGIN
    RETURN GREATEST(0, COALESCE(p_quantidade, 0)) * GREATEST(0, COALESCE(p_preco_unitario, 0));
END//

CREATE TRIGGER trg_produtos_valores_positivos_insert
BEFORE INSERT ON produtos
FOR EACH ROW
BEGIN
    IF NEW.permite_pedido THEN
        IF NEW.preco IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produtos para pedido online precisam de preço.';
        END IF;
        SET NEW.preco = ABS(NEW.preco);
    ELSE
        SET NEW.preco = NULL;
    END IF;
    SET NEW.estoque = ABS(NEW.estoque);
END//

CREATE TRIGGER trg_produtos_valores_positivos_update
BEFORE UPDATE ON produtos
FOR EACH ROW
BEGIN
    IF NEW.permite_pedido THEN
        IF NEW.preco IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produtos para pedido online precisam de preço.';
        END IF;
        SET NEW.preco = ABS(NEW.preco);
    ELSE
        SET NEW.preco = NULL;
    END IF;
    SET NEW.estoque = ABS(NEW.estoque);
END//

CREATE TRIGGER trg_pedido_produto_valores_positivos_update
BEFORE UPDATE ON pedido_produto
FOR EACH ROW
BEGIN
    SET NEW.quantidade = GREATEST(1, ABS(NEW.quantidade));
    SET NEW.preco_unitario = ABS(NEW.preco_unitario);
END//

CREATE TRIGGER trg_pedidos_valor_positivo_update
BEFORE UPDATE ON pedidos
FOR EACH ROW
BEGIN
    SET NEW.valor_total = ABS(NEW.valor_total);
END//

DELIMITER ;

CREATE OR REPLACE VIEW vw_pedido_itens_analiticos AS
SELECT
    p.id_pedido, p.data_pedido, p.status, c.id_cliente, c.nome AS cliente,
    pp.id_produto, pr.nome AS produto, cat.nome AS categoria,
    pp.quantidade, pp.preco_unitario,
    fn_calcular_valor_item(pp.quantidade, pp.preco_unitario) AS valor_item
FROM pedidos p
INNER JOIN clientes c ON c.id_cliente = p.id_cliente
INNER JOIN pedido_produto pp ON pp.id_pedido = p.id_pedido
INNER JOIN produtos pr ON pr.id_produto = pp.id_produto
INNER JOIN categorias cat ON cat.id_categoria = pr.id_categoria;

DELIMITER //

CREATE PROCEDURE sp_dashboard_indicadores(
    IN p_inicio DATETIME,
    IN p_fim DATETIME,
    IN p_busca VARCHAR(120),
    IN p_status VARCHAR(20),
    IN p_limite INT,
    IN p_offset INT
)
BEGIN
    DECLARE v_busca VARCHAR(122);
    DECLARE v_limite INT DEFAULT 10;
    DECLARE v_offset INT DEFAULT 0;

    SET v_busca = CONCAT('%', COALESCE(p_busca, ''), '%');
    SET v_limite = LEAST(50, GREATEST(5, COALESCE(p_limite, 10)));
    SET v_offset = GREATEST(0, COALESCE(p_offset, 0));

    SELECT * FROM vw_pedido_itens_analiticos
    WHERE data_pedido >= p_inicio AND data_pedido < p_fim
      AND (COALESCE(p_status, '') = '' OR status = p_status)
      AND (COALESCE(p_busca, '') = '' OR produto LIKE v_busca OR categoria LIKE v_busca OR cliente LIKE v_busca OR CAST(id_pedido AS CHAR) LIKE v_busca)
    ORDER BY data_pedido DESC, id_pedido DESC, id_produto ASC;

    WITH pedidos_consolidados AS (
        SELECT id_pedido, status, SUM(valor_item) AS total_pedido
        FROM vw_pedido_itens_analiticos
        WHERE data_pedido >= p_inicio AND data_pedido < p_fim
          AND (COALESCE(p_status, '') = '' OR status = p_status)
          AND (COALESCE(p_busca, '') = '' OR produto LIKE v_busca OR categoria LIKE v_busca OR cliente LIKE v_busca OR CAST(id_pedido AS CHAR) LIKE v_busca)
        GROUP BY id_pedido, status
    )
    SELECT status, COUNT(*) AS quantidade_pedidos, COALESCE(SUM(total_pedido), 0) AS valor_total
    FROM pedidos_consolidados
    GROUP BY status
    ORDER BY FIELD(status, 'pendente', 'aprovado', 'concluido', 'cancelado');

    WITH produto_totais AS (
        SELECT id_produto, produto, categoria, SUM(quantidade) AS quantidade_vendida, SUM(valor_item) AS faturamento
        FROM vw_pedido_itens_analiticos
        WHERE data_pedido >= p_inicio AND data_pedido < p_fim AND status <> 'cancelado'
          AND (COALESCE(p_status, '') = '' OR status = p_status)
          AND (COALESCE(p_busca, '') = '' OR produto LIKE v_busca OR categoria LIKE v_busca OR cliente LIKE v_busca OR CAST(id_pedido AS CHAR) LIKE v_busca)
        GROUP BY id_produto, produto, categoria
    )
    SELECT id_produto, produto, categoria, quantidade_vendida, faturamento
    FROM produto_totais
    ORDER BY quantidade_vendida DESC, faturamento DESC, produto ASC
    LIMIT 5;

    SELECT * FROM vw_pedido_itens_analiticos
    WHERE data_pedido >= p_inicio AND data_pedido < p_fim
      AND (COALESCE(p_status, '') = '' OR status = p_status)
      AND (COALESCE(p_busca, '') = '' OR produto LIKE v_busca OR categoria LIKE v_busca OR cliente LIKE v_busca OR CAST(id_pedido AS CHAR) LIKE v_busca)
    ORDER BY data_pedido DESC, id_pedido DESC, id_produto ASC
    LIMIT v_limite OFFSET v_offset;

    SELECT COUNT(*) AS total FROM vw_pedido_itens_analiticos
    WHERE data_pedido >= p_inicio AND data_pedido < p_fim
      AND (COALESCE(p_status, '') = '' OR status = p_status)
      AND (COALESCE(p_busca, '') = '' OR produto LIKE v_busca OR categoria LIKE v_busca OR cliente LIKE v_busca OR CAST(id_pedido AS CHAR) LIKE v_busca);
END//

DELIMITER ;

SELECT 'Rubrica aplicada com sucesso.' AS resultado;
