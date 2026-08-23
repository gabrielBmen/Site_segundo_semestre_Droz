<?php
$adminPagina = 'Pedidos';
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
$stmt = $pdo->query("SELECT p.id_pedido, c.nome AS cliente, p.data_pedido, p.valor_total, p.status FROM pedidos p INNER JOIN clientes c ON c.id_cliente = p.id_cliente ORDER BY p.data_pedido DESC");
$pedidos = $stmt->fetchAll();
?>
<div class="dashboard-card"><div class="table-responsive"><table class="table table-dark align-middle mb-0"><thead><tr><th>ID</th><th>Cliente</th><th>Data</th><th>Valor</th><th>Status</th></tr></thead><tbody>
<?php if (!$pedidos): ?><tr><td colspan="5" class="text-white-50">Nenhum pedido registrado.</td></tr><?php endif; ?>
<?php foreach ($pedidos as $pedido): ?><tr><td>#<?= (int)$pedido['id_pedido'] ?></td><td><?= e($pedido['cliente']) ?></td><td><?= e(date('d/m/Y H:i', strtotime($pedido['data_pedido']))) ?></td><td><?= e(number_format((float)$pedido['valor_total'],2,',','.')) ?></td><td><span class="badge text-bg-secondary"><?= e(ucfirst($pedido['status'])) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>