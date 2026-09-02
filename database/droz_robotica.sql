-- Banco completo: cria apenas a estrutura, os relacionamentos, índices e dados iniciais.
-- Depois de importar este arquivo, importe database/rubrica.sql.

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
    cep CHAR(8) NOT NULL,
    cpf CHAR(11) NOT NULL UNIQUE,
    cnpj CHAR(14) NOT NULL UNIQUE,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clientes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
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
    preco DECIMAL(10,2) NULL,
    estoque INT NOT NULL DEFAULT 0,
    permite_pedido BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_produto_preco CHECK (preco IS NULL OR preco >= 0),
    CONSTRAINT chk_produto_preco_condicional CHECK ((permite_pedido = TRUE AND preco IS NOT NULL AND preco >= 0) OR (permite_pedido = FALSE AND preco IS NULL)),
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
    CONSTRAINT fk_contatos_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_contatos_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE SET NULL ON UPDATE CASCADE
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

CREATE INDEX idx_produtos_categoria ON produtos(id_categoria);
CREATE INDEX idx_produtos_permite_pedido ON produtos(permite_pedido);
CREATE INDEX idx_clientes_usuario ON clientes(id_usuario);
CREATE INDEX idx_contatos_cliente ON contatos(id_cliente);
CREATE INDEX idx_contatos_produto ON contatos(id_produto);
CREATE INDEX idx_pedidos_cliente ON pedidos(id_cliente);
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedidos_data ON pedidos(data_pedido);
CREATE INDEX idx_imagens_produto ON imagens_produto(id_produto);

INSERT INTO usuarios (nome, email, senha, tipo, ativo)
VALUES ('Administrador DROZ', 'admin@drozrobotica.com', '$2y$12$Y8qxTQ2jVTdNaVSZdjmt6.T.A0QBEEXxJSjNjojerYE6m5a/X3x0u', 'admin', TRUE);

INSERT INTO categorias (nome)
VALUES ('Soldagem robotizada'), ('Automacao industrial'), ('Projetos especiais'), ('Servicos');

INSERT INTO produtos (id_categoria, nome, slug, descricao, preco, estoque, permite_pedido, ativo)
VALUES
(1, 'Célula Robotizada CSR1', 'celula-robotizada-csr1', 'Célula padrão para soldagem robotizada com mesa rotativa e foco em alta produtividade.', NULL, 3, FALSE, TRUE),
(2, 'Robô Industrial Integrado', 'robo-industrial-integrado', 'Pacote com robô industrial, engenharia de integração e suporte para operação assistida.', NULL, 5, FALSE, TRUE),
(3, 'Célula de Aproximação', 'celula-de-aproximacao', 'Estrutura compacta para automação de tarefas repetitivas com operação segura e escalável.', NULL, 2, FALSE, TRUE),
(4, 'Treinamento de Programação', 'treinamento-programacao', 'Capacitação para operadores e programadores com foco em robôs industriais e célula de solda.', NULL, 12, FALSE, TRUE);

INSERT INTO imagens_produto (id_produto, caminho, principal) VALUES
(1, 'assets/imagens/lado esquerdo CSR1.jpg', TRUE),
(1, 'assets/imagens/Frente CSR1.jpg', FALSE),
(1, 'assets/imagens/robo CSR1.jpg', FALSE),
(2, 'assets/imagens/robo CSR1.jpg', TRUE),
(3, 'assets/imagens/Frente CSR1.jpg', TRUE),
(4, 'assets/imagens/Curso.jpg', TRUE);
