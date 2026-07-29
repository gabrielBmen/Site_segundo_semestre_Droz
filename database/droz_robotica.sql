DROP DATABASE IF EXISTS droz_robotica;

CREATE DATABASE droz_robotica
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE droz_robotica;

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120),
    email VARCHAR(120),
    telefone VARCHAR(30),
    mensagem TEXT
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80)
);

CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    nome VARCHAR(120),
    slug VARCHAR(140),
    descricao TEXT,
    preco DECIMAL(10,2),
    estoque INT,
    imagem VARCHAR(255),

    FOREIGN KEY (id_categoria)
    REFERENCES categorias(id_categoria)
);

CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    data_pedido DATETIME,
    valor_total DECIMAL(10,2),
    
    FOREIGN KEY (id_cliente)
    REFERENCES clientes(id_cliente)
);

CREATE TABLE pedido_produto (
    id_pedido INT,
    id_produto INT,
    quantidade INT,
    preco_unitario DECIMAL(10,2),

    PRIMARY KEY (id_pedido, id_produto),

    FOREIGN KEY (id_pedido)
    REFERENCES pedidos(id_pedido),

    FOREIGN KEY (id_produto)
    REFERENCES produtos(id_produto)
);

INSERT INTO categorias (nome)
VALUES
('Soldagem robotizada'),
('Automacao industrial'),
('Projetos especiais'),
('Servicos');

INSERT INTO produtos
(id_categoria, nome, slug, descricao, preco, estoque, imagem)
VALUES

(1,
'Célula Robotizada CSR1',
'celula-robotizada-csr1',
'Célula padrão para soldagem robotizada com mesa rotativa e foco em alta produtividade.',
629000.00,
3,
'assets/lado esquerdo CSR1.jpg'), /* Substitua pelo nome real do arquivo */

(2,
'Robô Industrial Integrado',
'robo-industrial-integrado',
'Pacote com robô industrial, engenharia de integração e suporte para operação assistida.',
239000.00,
5,
'assets/robo CSR1.jpg'),

(3,
'Célula de Aproximação',
'celula-de-aproximacao',
'Estrutura compacta para automação de tarefas repetitivas com operação segura e escalável.',
350000.00,
2,
'assets/Frente CSR1.jpg'),

(4,
'Treinamento de Programação',
'treinamento-programacao',
'Capacitação para operadores e programadores com foco em robôs industriais e célula de solda.',
4900.00,
12,
'assets/Curso.jpg'); 