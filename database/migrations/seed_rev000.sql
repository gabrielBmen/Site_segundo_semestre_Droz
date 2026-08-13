USE droz_robotica;

INSERT INTO categorias (nome)
VALUES
('Soldagem robotizada'),
('Automação industrial'),
('Projetos especiais'),
('Serviços');

INSERT INTO produtos
(
    id_categoria,
    nome,
    slug,
    descricao,
    preco,
    estoque
)
VALUES

(
    1,
    'Célula Robotizada CSR1',
    'celula-robotizada-csr1',
    'Célula padrão para soldagem robotizada com mesa rotativa e foco em alta produtividade.',
    629000.00,
    3
),

(
    2,
    'Robô Industrial Integrado',
    'robo-industrial-integrado',
    'Pacote com robô industrial, engenharia de integração e suporte para operação assistida.',
    239000.00,
    5
),

(
    3,
    'Célula de Aproximação',
    'celula-de-aproximacao',
    'Estrutura compacta para automação de tarefas repetitivas com operação segura e escalável.',
    350000.00,
    2
),

(
    4,
    'Treinamento de Programação',
    'treinamento-programacao',
    'Capacitação para operadores e programadores com foco em robôs industriais e célula de solda.',
    4900.00,
    12
);

INSERT INTO imagens_produto
(
    id_produto,
    caminho,
    principal
)
VALUES

(
    1,
    'assets/imagens/lado esquerdo CSR1.jpg',
    TRUE
),

(
    1,
    'assets/imagens/Frente CSR1.jpg',
    FALSE
),

(
    1,
    'assets/imagens/robo CSR1.jpg',
    FALSE
),

(
    2,
    'assets/imagens/robo CSR1.jpg',
    TRUE
),

(
    3,
    'assets/imagens/Frente CSR1.jpg',
    TRUE
),

(
    4,
    'assets/imagens/Curso.jpg',
    TRUE
);