-- Execute uma única vez em instalações que já possuem o banco de dados.
-- Para uma instalação nova, use apenas droz_robotica.sql.

USE droz_robotica;

ALTER TABLE produtos
    ADD COLUMN permite_pedido BOOLEAN NOT NULL DEFAULT FALSE AFTER estoque;

CREATE INDEX idx_produtos_permite_pedido ON produtos(permite_pedido);

-- Por segurança, os produtos já cadastrados continuam sendo atendidos por orçamento.
-- Marque "Disponível para pedido online" no painel para os itens que podem ser vendidos.
