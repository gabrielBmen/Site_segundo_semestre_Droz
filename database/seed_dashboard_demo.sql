USE droz_robotica;

-- Dados opcionais apenas para demonstração da dashboard.
-- Podem ser removidos sem afetar o funcionamento da aplicação.

INSERT INTO usuarios (nome, email, senha, tipo, ativo)
VALUES ('Cliente Demo', 'cliente.demo@drozrobotica.com', '$2y$12$Y8qxTQ2jVTdNaVSZdjmt6.T.A0QBEEXxJSjNjojerYE6m5a/X3x0u', 'cliente', TRUE);

INSERT INTO clientes (id_usuario, nome, email, telefone)
SELECT id_usuario, nome, email, '(44) 99999-0000'
FROM usuarios
WHERE email = 'cliente.demo@drozrobotica.com'
AND NOT EXISTS (SELECT 1 FROM clientes c WHERE c.email = 'cliente.demo@drozrobotica.com');

INSERT INTO pedidos (id_cliente, status, valor_total)
SELECT id_cliente, 'concluido', 868000.00
FROM clientes
WHERE email = 'cliente.demo@drozrobotica.com'
LIMIT 1;

SET @pedido_demo = LAST_INSERT_ID();

INSERT INTO pedido_produto (id_pedido, id_produto, quantidade, preco_unitario)
SELECT @pedido_demo, id_produto, 1, 629000.00 FROM produtos WHERE slug = 'celula-robotizada-csr1';

INSERT INTO pedido_produto (id_pedido, id_produto, quantidade, preco_unitario)
SELECT @pedido_demo, id_produto, 1, 239000.00 FROM produtos WHERE slug = 'robo-industrial-integrado';
