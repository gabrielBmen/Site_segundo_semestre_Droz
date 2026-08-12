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

if (!validarListaProdutos($catalogoProdutos)) {
    $catalogoProdutos = [];
}
?>
