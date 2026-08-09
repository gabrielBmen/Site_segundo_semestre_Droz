DROP DATABASE IF EXISTS droz_robotica;

CREATE DATABASE droz_robotica
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE droz_robotica;


-- =========================================================
-- USUÁRIOS
-- =========================================================

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(120) NOT NULL,

    email VARCHAR(120) NOT NULL UNIQUE,

    senha VARCHAR(255) NOT NULL,

    tipo ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',

    ativo BOOLEAN NOT NULL DEFAULT TRUE,

    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    data_atualizacao DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =========================================================
-- CLIENTES
-- =========================================================

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL UNIQUE,

    nome VARCHAR(120) NOT NULL,

    email VARCHAR(120) NOT NULL UNIQUE,

    telefone VARCHAR(30),

    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    data_atualizacao DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_clientes_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- =========================================================
-- CONTATOS / SOLICITAÇÕES
-- =========================================================

CREATE TABLE contatos (
    id_contato INT AUTO_INCREMENT PRIMARY KEY,

    id_cliente INT NULL,

    nome VARCHAR(120) NOT NULL,

    email VARCHAR(120) NOT NULL,

    telefone VARCHAR(30),

    interesse VARCHAR(400),

    mensagem TEXT,

    data_contato DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_contatos_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);


-- =========================================================
-- CATEGORIAS
-- =========================================================

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(80) NOT NULL UNIQUE,

    ativo BOOLEAN NOT NULL DEFAULT TRUE,

    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    data_atualizacao DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =========================================================
-- PRODUTOS
-- =========================================================

CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,

    id_categoria INT NOT NULL,

    nome VARCHAR(120) NOT NULL,

    slug VARCHAR(140) NOT NULL UNIQUE,

    descricao TEXT,

    preco DECIMAL(10,2) NOT NULL,

    estoque INT NOT NULL DEFAULT 0,

    ativo BOOLEAN NOT NULL DEFAULT TRUE,

    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    data_atualizacao DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT chk_produto_preco
        CHECK (preco >= 0),

    CONSTRAINT chk_produto_estoque
        CHECK (estoque >= 0),

    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- =========================================================
-- IMAGENS DOS PRODUTOS
-- =========================================================

CREATE TABLE imagens_produto (
    id_imagem INT AUTO_INCREMENT PRIMARY KEY,

    id_produto INT NOT NULL,

    caminho VARCHAR(255) NOT NULL,

    principal BOOLEAN NOT NULL DEFAULT FALSE,

    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_imagens_produto
        FOREIGN KEY (id_produto)
        REFERENCES produtos(id_produto)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- =========================================================
-- PEDIDOS
-- =========================================================

CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,

    id_cliente INT NOT NULL,

    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0,

    status ENUM(
        'pendente',
        'aprovado',
        'cancelado',
        'concluido'
    ) NOT NULL DEFAULT 'pendente',

    CONSTRAINT chk_pedido_valor
        CHECK (valor_total >= 0),

    CONSTRAINT fk_pedidos_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- =========================================================
-- PRODUTOS DO PEDIDO
-- =========================================================

CREATE TABLE pedido_produto (
    id_pedido INT NOT NULL,

    id_produto INT NOT NULL,

    quantidade INT NOT NULL,

    preco_unitario DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (id_pedido, id_produto),

    CONSTRAINT chk_pedido_quantidade
        CHECK (quantidade > 0),

    CONSTRAINT chk_pedido_preco
        CHECK (preco_unitario >= 0),

    CONSTRAINT fk_pedido_produto_pedido
        FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_pedido_produto_produto
        FOREIGN KEY (id_produto)
        REFERENCES produtos(id_produto)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- =========================================================
-- ÍNDICES
-- =========================================================

CREATE INDEX idx_produtos_categoria
ON produtos(id_categoria);

CREATE INDEX idx_clientes_usuario
ON clientes(id_usuario);

CREATE INDEX idx_contatos_cliente
ON contatos(id_cliente);

CREATE INDEX idx_pedidos_cliente
ON pedidos(id_cliente);

CREATE INDEX idx_pedidos_status
ON pedidos(status);

CREATE INDEX idx_imagens_produto
ON imagens_produto(id_produto);