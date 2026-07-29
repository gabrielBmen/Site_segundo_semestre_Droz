<?php
if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('moeda')) {
    function moeda($valor) {
        return 'R$ ' . number_format((float)$valor, 2, ',', '.');
    }
}

if (!function_exists('aplicarDesconto')) {
    function aplicarDesconto(float $preco, float $percentual): float {
        if ($preco < 0 || $percentual < 0 || $percentual > 100) {
            return 0;
        }
        return $preco - ($preco * ($percentual / 100));
    }
}

if (!function_exists('filtrarProdutosPorPreco')) {
    function filtrarProdutosPorPreco(array $lista, float $minimo): array {
        $resultado = [];

        foreach ($lista as $produto) {
            if (isset($produto['preco']) && $produto['preco'] >= $minimo) {
                $resultado[] = $produto;
            }
        }

        return $resultado;
    }
}

if (!function_exists('buscarProdutoPorSlug')) {
    function buscarProdutoPorSlug(array $lista, string $slug): ?array {
        foreach ($lista as $produto) {
            if (($produto['slug'] ?? '') === $slug) {
                return $produto;
            }
        }
        return null;
    }
}

if (!function_exists('validarListaProdutos')) {
    function validarListaProdutos(array $lista): bool {
        if (empty($lista)) {
            return false;
        }

        foreach ($lista as $produto) {
            if (
                !isset($produto['nome'], $produto['preco']) ||
                trim((string)$produto['nome']) === '' ||
                (float)$produto['preco'] < 0
            ) {
                return false;
            }
        }

        return true;
    }
}

$produtosDestaque = [
    [
        'slug' => 'celula-robotizada-csr1',
        'nome' => 'Célula Robotizada CSR1',
        'categoria' => 'Soldagem robotizada',
        'preco' => 629000,
        'descricao' => 'Célula padrão para soldagem robotizada com foco em produtividade, repetibilidade e robustez industrial.',
        'imagem' => 'assets/lado esquerdo CSR1.jpg',
        'beneficios' => ['Alta repetibilidade', 'Estrutura industrial', 'Treinamento técnico']
    ],
    [
        'slug' => 'robô-industrial-integrado',
        'nome' => 'Robô Industrial Integrado',
        'categoria' => 'Automação industrial',
        'preco' => 239000,
        'descricao' => 'Solução com robô industrial, integração elétrica e programação para linhas de produção e solda.',
        'imagem' => 'assets/robo CSR1.jpg',
        'beneficios' => ['Integração sob medida', 'NR-12', 'Maior eficiência']
    ],
    [
        'slug' => 'estrutura-robotica-customizada',
        'nome' => 'Estrutura Robótica Customizada',
        'categoria' => 'Projetos especiais',
        'preco' => 350000,
        'descricao' => 'Projeto sob medida para empresas que precisam de automação customizada e evolução de processo.',
        'imagem' => 'assets/Frente CSR1.jpg',
        'beneficios' => ['Sob medida', 'Projeto consultivo', 'Suporte técnico']
    ],
];

$catalogoProdutos = [
    [
        'slug' => 'celula-robotizada-csr1',
        'nome' => 'Célula Robotizada CSR1',
        'categoria' => 'Soldagem robotizada',
        'preco' => 629000,
        'estoque' => 3,
        'descricao' => 'Célula padrão para soldagem robotizada com mesa rotativa e foco em alta produtividade.',
        'imagem' => 'assets/lado esquerdo CSR1.jpg',
    ],
    [
        'slug' => 'robô-industrial-integrado',
        'nome' => 'Robô Industrial Integrado',
        'categoria' => 'Automação industrial',
        'preco' => 239000,
        'estoque' => 5,
        'descricao' => 'Pacote com robô industrial, engenharia de integração e suporte para operação assistida.',
        'imagem' => 'assets/robo CSR1.jpg',
    ],
    [
        'slug' => 'célula-de-aproximação',
        'nome' => 'Célula de Aproximação',
        'categoria' => 'Projetos especiais',
        'preco' => 350000,
        'estoque' => 2,
        'descricao' => 'Estrutura compacta para automação de tarefas repetitivas com operação segura e escalável.',
        'imagem' => 'assets/Frente CSR1.jpg',
    ],
    [
        'slug' => 'treinamento-programacao',
        'nome' => 'Treinamento de Programação',
        'categoria' => 'Serviços',
        'preco' => 4900,
        'estoque' => 12,
        'descricao' => 'Capacitação para operadores e programadores com foco em robôs industriais e célula de solda.',
        'imagem' => 'assets/Curso.jpg',
    ],
];

if (!validarListaProdutos($catalogoProdutos)) {
    $catalogoProdutos = [];
}
?>
