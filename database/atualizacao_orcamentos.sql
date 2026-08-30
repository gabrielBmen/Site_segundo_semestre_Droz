-- Execute uma única vez em instalações que já possuem o banco de dados.
-- Para uma instalação nova, use apenas droz_robotica.sql.

USE droz_robotica;

ALTER TABLE contatos
    ADD COLUMN id_produto INT NULL AFTER id_cliente,
    ADD CONSTRAINT fk_contatos_produto
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE INDEX idx_contatos_produto ON contatos(id_produto);
