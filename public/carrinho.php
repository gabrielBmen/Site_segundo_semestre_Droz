<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/PedidoModel.php';

exigirLogin('carrinho.php');

if (usuarioAdmin()) {
    header('Location: /admin/pedidos.php');
    exit;
}

$usuario = usuarioAtual();
$usuarioModel = new UsuarioModel($pdo);
$cliente = $usuarioModel->buscarClientePorUsuario((int) $usuario['id_usuario']);

if (!$cliente) {
    http_response_code(403);
    exit('Cadastro de cliente não encontrado.');
}

$pedidoModel = new PedidoModel($pdo);
$_SESSION['carrinho'] = is_array($_SESSION['carrinho'] ?? null) ? $_SESSION['carrinho'] : [];

function redirecionarCarrinho(string $mensagem, string $tipo = 'success'): void
{
    $_SESSION['flash_carrinho'] = ['mensagem' => $mensagem, 'tipo' => $tipo];
    header('Location: carrinho.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        redirecionarCarrinho('Sua sessão expirou. Atualize a página e tente novamente.', 'danger');
    }

    $acao = (string) ($_POST['acao'] ?? '');

    if ($acao === 'adicionar') {
        $idProduto = (int) ($_POST['id_produto'] ?? 0);
        $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));
        $produto = $pedidoModel->produtoDisponivelParaPedido($idProduto);

        if (!$produto || (int) $produto['estoque'] <= 0) {
            redirecionarCarrinho('Este produto não está disponível para pedido online.', 'warning');
        }

        $atual = (int) ($_SESSION['carrinho'][$idProduto] ?? 0);
        $_SESSION['carrinho'][$idProduto] = min($atual + $quantidade, (int) $produto['estoque']);
        redirecionarCarrinho('Produto adicionado ao pedido.');
    }

    if ($acao === 'atualizar') {
        $quantidades = $_POST['quantidades'] ?? [];
        if (is_array($quantidades)) {
            foreach ($quantidades as $idProduto => $quantidade) {
                $idProduto = (int) $idProduto;
                $quantidade = (int) $quantidade;

                if ($idProduto <= 0) {
                    continue;
                }

                if ($quantidade <= 0) {
                    unset($_SESSION['carrinho'][$idProduto]);
                    continue;
                }

                $_SESSION['carrinho'][$idProduto] = min($quantidade, 999);
            }
        }
        redirecionarCarrinho('Quantidades atualizadas.');
    }

    if ($acao === 'remover') {
        unset($_SESSION['carrinho'][(int) ($_POST['id_produto'] ?? 0)]);
        redirecionarCarrinho('Produto removido do pedido.');
    }

    if ($acao === 'finalizar') {
        if ($_SESSION['carrinho'] === []) {
            redirecionarCarrinho('Adicione ao menos um produto antes de finalizar.', 'warning');
        }

        try {
            $idPedido = $pedidoModel->criarPedido((int) $cliente['id_cliente'], $_SESSION['carrinho']);
            $_SESSION['carrinho'] = [];
            redirecionarCarrinho("Pedido #{$idPedido} enviado com sucesso. O status inicial é pendente.");
        } catch (Throwable $e) {
            redirecionarCarrinho($e->getMessage(), 'danger');
        }
    }

    redirecionarCarrinho('Ação inválida.', 'danger');
}

$flash = $_SESSION['flash_carrinho'] ?? null;
unset($_SESSION['flash_carrinho']);

$itens = $pedidoModel->buscarItensDoCarrinho(array_keys($_SESSION['carrinho']));
$itensPorId = [];
foreach ($itens as $item) {
    $itensPorId[(int) $item['id_produto']] = $item;
}

$valorTotal = 0.0;
foreach ($_SESSION['carrinho'] as $idProduto => $quantidade) {
    if (!isset($itensPorId[(int) $idProduto])) {
        unset($_SESSION['carrinho'][$idProduto]);
        continue;
    }
    $valorTotal += (float) $itensPorId[(int) $idProduto]['preco'] * (int) $quantidade;
}

$tituloPagina = 'Meu pedido';
$descricaoPagina = 'Revise os produtos selecionados e envie seu pedido para a DROZ Robótica.';
$paginaAtiva = 'carrinho';

include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h1 class="section-title text-start mb-2">Meu pedido</h1>
                <p class="section-subtitle text-start mb-0">Confira as quantidades antes de enviar. O pedido será analisado pela equipe.</p>
            </div>
            <a href="produtos.php" class="btn btn-outline-light">Continuar no catálogo</a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['tipo'] ?? 'success') ?> rounded-4 border-0">
                <?= e($flash['mensagem'] ?? '') ?>
            </div>
        <?php endif; ?>

        <?php if ($itensPorId === []): ?>
            <div class="glass-card rounded-4 p-5 text-center">
                <i class="bi bi-cart-x display-5 text-white-50"></i>
                <h2 class="h4 mt-3">Seu pedido está vazio</h2>
                <p class="text-white-50">Produtos disponíveis para pedido online podem ser adicionados pelo catálogo.</p>
                <a href="produtos.php" class="btn btn-primary">Ver catálogo</a>
            </div>
        <?php else: ?>
            <form method="POST" class="glass-card rounded-4 p-4 mb-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="acao" value="atualizar">

                <div class="table-responsive">
                    <table class="table table-dark align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço unitário</th>
                                <th style="min-width: 130px;">Quantidade</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itensPorId as $idProduto => $item): ?>
                                <?php
                                $quantidade = (int) ($_SESSION['carrinho'][$idProduto] ?? 0);
                                $disponivel = (bool) $item['ativo'] && (bool) $item['permite_pedido'] && (int) $item['estoque'] > 0;
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($item['nome']) ?></div>
                                        <small class="text-white-50"><?= e($item['categoria']) ?> · Estoque atual: <?= (int) $item['estoque'] ?></small>
                                        <?php if (!$disponivel): ?>
                                            <div class="small text-warning mt-1">Não está mais disponível para pedido online.</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= moeda($item['preco']) ?></td>
                                    <td>
                                        <input type="number" class="form-control" name="quantidades[<?= (int) $idProduto ?>]" min="0" max="<?= max(0, (int) $item['estoque']) ?>" value="<?= $quantidade ?>" <?= !$disponivel ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="fw-semibold"><?= moeda((float) $item['preco'] * $quantidade) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" name="quantidades[<?= (int) $idProduto ?>]" value="0">Remover</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-outline-light">Atualizar quantidades</button>
                </div>
            </form>

            <div class="glass-card rounded-4 p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="text-white-50 small">Total do pedido</div>
                    <div class="display-6 fw-bold"><?= moeda($valorTotal) ?></div>
                    <small class="text-white-50">O preço e o estoque são conferidos novamente no envio.</small>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="acao" value="finalizar">
                    <button type="submit" class="btn btn-primary btn-lg">Enviar pedido</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
