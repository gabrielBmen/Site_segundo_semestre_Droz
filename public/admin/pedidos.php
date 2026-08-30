<?php

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/PedidoModel.php';

exigirAdmin();

$pedidoModel = new PedidoModel($pdo);
$erro = '';
$sucesso = (string) ($_GET['sucesso'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } else {
        $idPedido = (int) ($_POST['id_pedido'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');

        if ($pedidoModel->atualizarStatus($idPedido, $status)) {
            header('Location: pedidos.php?sucesso=' . rawurlencode("Status do pedido #{$idPedido} atualizado."));
            exit;
        }

        $erro = 'Não foi possível atualizar o status do pedido.';
    }
}

$pedidos = $pedidoModel->listarParaAdmin();
$valorTotal = array_reduce(
    $pedidos,
    fn (float $total, array $pedido): float => $total + (float) $pedido['valor_total'],
    0.0
);
$pendentes = count(array_filter($pedidos, fn (array $pedido): bool => $pedido['status'] === 'pendente'));
$itensVendidos = array_reduce(
    $pedidos,
    fn (int $total, array $pedido): int => $total + array_sum(array_column($pedido['itens'], 'quantidade')),
    0
);
$statusLabels = [
    'pendente' => 'Pendente',
    'aprovado' => 'Aprovado',
    'concluido' => 'Concluído',
    'cancelado' => 'Cancelado',
];
$statusClasses = [
    'pendente' => 'text-bg-warning',
    'aprovado' => 'text-bg-info',
    'concluido' => 'text-bg-success',
    'cancelado' => 'text-bg-secondary',
];

$adminPagina = 'Pedidos';
include __DIR__ . '/includes/header.php';
?>

<?php if ($sucesso !== ''): ?>
    <div class="alert alert-success"><?= e($sucesso) ?></div>
<?php endif; ?>

<?php if ($erro !== ''): ?>
    <div class="alert alert-danger"><?= e($erro) ?></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-receipt"></i></div>
            <div class="text-white-50 small">Pedidos recebidos</div>
            <div class="metric-value"><?= count($pedidos) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="text-white-50 small">Aguardando análise</div>
            <div class="metric-value"><?= $pendentes ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="text-white-50 small">Valor total dos pedidos</div>
            <div class="metric-value"><?= moeda($valorTotal) ?></div>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="text-uppercase small text-white-50">Análise operacional</div>
            <h2 class="h5 mb-0">Pedidos e itens solicitados</h2>
        </div>
        <span class="badge text-bg-secondary"><?= $itensVendidos ?> itens</span>
    </div>

    <div class="table-responsive">
        <table class="table table-dark align-middle mb-0">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Itens</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th class="text-end">Atualizar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$pedidos): ?>
                    <tr><td colspan="6" class="text-white-50">Nenhum pedido registrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold">#<?= (int) $pedido['id_pedido'] ?></div>
                            <small class="text-white-50"><?= e(date('d/m/Y H:i', strtotime($pedido['data_pedido']))) ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= e($pedido['cliente']) ?></div>
                            <small class="text-white-50 d-block"><?= e($pedido['email']) ?></small>
                            <?php if (!empty($pedido['telefone'])): ?>
                                <small class="text-white-50"><?= e($pedido['telefone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="min-width: 220px;">
                            <?php foreach ($pedido['itens'] as $item): ?>
                                <div class="small mb-1">
                                    <strong><?= (int) $item['quantidade'] ?>×</strong> <?= e($item['produto']) ?>
                                    <span class="text-white-50">(<?= moeda($item['preco_unitario']) ?>)</span>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td class="fw-semibold text-nowrap"><?= moeda($pedido['valor_total']) ?></td>
                        <td>
                            <span class="badge <?= e($statusClasses[$pedido['status']] ?? 'text-bg-secondary') ?>">
                                <?= e($statusLabels[$pedido['status']] ?? ucfirst($pedido['status'])) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" class="d-flex justify-content-end gap-2">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="id_pedido" value="<?= (int) $pedido['id_pedido'] ?>">
                                <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary" aria-label="Status do pedido #<?= (int) $pedido['id_pedido'] ?>">
                                    <?php foreach ($statusLabels as $valorStatus => $rotuloStatus): ?>
                                        <option value="<?= e($valorStatus) ?>" <?= $pedido['status'] === $valorStatus ? 'selected' : '' ?>><?= e($rotuloStatus) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-light" type="submit" title="Salvar status">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
