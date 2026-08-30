
DROP DATABASE IF EXISTS droz_robotica;
CREATE DATABASE droz_robotica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE droz_robotica;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    foto_perfil VARCHAR(255) NULL,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    telefone VARCHAR(30),
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clientes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE contatos (
    id_contato INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NULL,
    id_produto INT NULL,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    telefone VARCHAR(30),
    interesse VARCHAR(400),
    mensagem TEXT,
    data_contato DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contatos_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    permite_pedido BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_produto_preco CHECK (preco >= 0),
    CONSTRAINT chk_produto_estoque CHECK (estoque >= 0),
    CONSTRAINT fk_produtos_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE imagens_produto (
    id_imagem INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    caminho VARCHAR(255) NOT NULL,
    principal BOOLEAN NOT NULL DEFAULT FALSE,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imagens_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pendente','aprovado','cancelado','concluido') NOT NULL DEFAULT 'pendente',
    CONSTRAINT chk_pedido_valor CHECK (valor_total >= 0),
    CONSTRAINT fk_pedidos_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE pedido_produto (
    id_pedido INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_pedido, id_produto),
    CONSTRAINT chk_pedido_quantidade CHECK (quantidade > 0),
    CONSTRAINT chk_pedido_preco CHECK (preco_unitario >= 0),
    CONSTRAINT fk_pedido_produto_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pedido_produto_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT ON UPDATE CASCADE
);

ALTER TABLE contatos
    ADD CONSTRAINT fk_contatos_produto
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE INDEX idx_produtos_categoria ON produtos(id_categoria);
CREATE INDEX idx_produtos_permite_pedido ON produtos(permite_pedido);
CREATE INDEX idx_clientes_usuario ON clientes(id_usuario);
CREATE INDEX idx_contatos_cliente ON contatos(id_cliente);
CREATE INDEX idx_contatos_produto ON contatos(id_produto);
CREATE INDEX idx_pedidos_cliente ON pedidos(id_cliente);
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedidos_data ON pedidos(data_pedido);
CREATE INDEX idx_imagens_produto ON imagens_produto(id_produto);

DELIMITER //

CREATE FUNCTION fn_calcular_valor_item(
    p_quantidade INT,
    p_preco_unitario DECIMAL(10,2)
)
RETURNS DECIMAL(12,2)
DETERMINISTIC
NO SQL
BEGIN
    RETURN GREATEST(0, COALESCE(p_quantidade, 0))
        * GREATEST(0, COALESCE(p_preco_unitario, 0));
END//

CREATE TRIGGER trg_produtos_valores_positivos_update
BEFORE UPDATE ON produtos
FOR EACH ROW
BEGIN
    SET NEW.preco = ABS(NEW.preco);
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
    p.id_pedido,
    p.data_pedido,
    p.status,
    c.id_cliente,
    c.nome AS cliente,
    pp.id_produto,
    pr.nome AS produto,
    cat.nome AS categoria,
    pp.quantidade,
    pp.preco_unitario,
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

    SELECT *
    FROM vw_pedido_itens_analiticos
    WHERE data_pedido >= p_inicio
      AND data_pedido < p_fim
      AND (COALESCE(p_status, '') = '' OR status = p_status)
      AND (
          COALESCE(p_busca, '') = ''
          OR produto LIKE v_busca
          OR categoria LIKE v_busca
          OR cliente LIKE v_busca
          OR CAST(id_pedido AS CHAR) LIKE v_busca
      )
    ORDER BY data_pedido DESC, id_pedido DESC, id_produto ASC;

    WITH pedidos_consolidados AS (
        SELECT id_pedido, status, SUM(valor_item) AS total_pedido
        FROM vw_pedido_itens_analiticos
        WHERE data_pedido >= p_inicio
          AND data_pedido < p_fim
          AND (COALESCE(p_status, '') = '' OR status = p_status)
          AND (
              COALESCE(p_busca, '') = ''
              OR produto LIKE v_busca
              OR categoria LIKE v_busca
              OR cliente LIKE v_busca
              OR CAST(id_pedido AS CHAR) LIKE v_busca
          )
        GROUP BY id_pedido, status
    )
    SELECT status, COUNT(*) AS quantidade_pedidos, COALESCE(SUM(total_pedido), 0) AS valor_total
    FROM pedidos_consolidados
    GROUP BY status
    ORDER BY FIELD(status, 'pendente', 'aprovado', 'concluido', 'cancelado');

    WITH produto_totais AS (
        SELECT
            id_produto,
            produto,
            categoria,
            SUM(quantidade) AS quantidade_vendida,
            SUM(valor_item) AS faturamento
        FROM vw_pedido_itens_analiticos
        WHERE data_pedido >= p_inicio
          AND data_pedido < p_fim
          AND status <> 'cancelado'
          AND (COALESCE(p_status, '') = '' OR status = p_status)
          AND (
              COALESCE(p_busca, '') = ''
              OR produto LIKE v_busca
              OR categoria LIKE v_busca
              OR cliente LIKE v_busca
              OR CAST(id_pedido AS CHAR) LIKE v_busca
          )
        GROUP BY id_produto, produto, categoria
    )
    SELECT id_produto, produto, categoria, quantidade_vendida, faturamento
    FROM produto_totais
    ORDER BY quantidade_vendida DESC, faturamento DESC, produto ASC
    LIMIT 5;

    SELECT *
    FROM vw_pedido_itens_analiticos
    WHERE data_pedido >= p_inicio
      AND data_pedido < p_fim
      AND (COALESCE(p_status, '') = '' OR status = p_status)
      AND (
          COALESCE(p_busca, '') = ''
          OR produto LIKE v_busca
          OR categoria LIKE v_busca
          OR cliente LIKE v_busca
          OR CAST(id_pedido AS CHAR) LIKE v_busca
      )
    ORDER BY data_pedido DESC, id_pedido DESC, id_produto ASC
    LIMIT v_limite OFFSET v_offset;

    SELECT COUNT(*) AS total
    FROM vw_pedido_itens_analiticos
    WHERE data_pedido >= p_inicio
      AND data_pedido < p_fim
      AND (COALESCE(p_status, '') = '' OR status = p_status)
      AND (
          COALESCE(p_busca, '') = ''
          OR produto LIKE v_busca
          OR categoria LIKE v_busca
          OR cliente LIKE v_busca
          OR CAST(id_pedido AS CHAR) LIKE v_busca
      );
END//

DELIMITER ;

INSERT INTO usuarios (nome, email, senha, tipo, ativo)
VALUES ('Administrador DROZ', 'admin@drozrobotica.com', '$2y$12$Y8qxTQ2jVTdNaVSZdjmt6.T.A0QBEEXxJSjNjojerYE6m5a/X3x0u', 'admin', TRUE);

INSERT INTO categorias (nome)
VALUES ('Soldagem robotizada'), ('Automacao industrial'), ('Projetos especiais'), ('Servicos');

INSERT INTO produtos (id_categoria, nome, slug, descricao, preco, estoque, permite_pedido, ativo)
VALUES
(1, 'Célula Robotizada CSR1', 'celula-robotizada-csr1', 'Célula padrão para soldagem robotizada com mesa rotativa e foco em alta produtividade.', 629000.00, 3, FALSE, TRUE),
(2, 'Robô Industrial Integrado', 'robo-industrial-integrado', 'Pacote com robô industrial, engenharia de integração e suporte para operação assistida.', 239000.00, 5, FALSE, TRUE),
(3, 'Célula de Aproximação', 'celula-de-aproximacao', 'Estrutura compacta para automação de tarefas repetitivas com operação segura e escalável.', 350000.00, 2, FALSE, TRUE),
(4, 'Treinamento de Programação', 'treinamento-programacao', 'Capacitação para operadores e programadores com foco em robôs industriais e célula de solda.', 4900.00, 12, FALSE, TRUE);

INSERT INTO imagens_produto (id_produto, caminho, principal) VALUES
(1, 'assets/imagens/lado esquerdo CSR1.jpg', TRUE),
(1, 'assets/imagens/Frente CSR1.jpg', FALSE),
(1, 'assets/imagens/robo CSR1.jpg', FALSE),
(2, 'assets/imagens/robo CSR1.jpg', TRUE),
(3, 'assets/imagens/Frente CSR1.jpg', TRUE),
(4, 'assets/imagens/Curso.jpg', TRUE);
