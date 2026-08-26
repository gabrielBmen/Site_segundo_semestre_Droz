<?php
$adminPagina = 'Categorias';
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
$stmt = $pdo->query("SELECT id_categoria, nome, ativo FROM categorias ORDER BY id_categoria ASC");
$categorias = $stmt->fetchAll();
?>
<div class="dashboard-card"><div class="table-responsive"><table class="table table-dark align-middle mb-0"><thead><tr><th>ID</th><th>Categoria</th><th>Status</th></tr></thead><tbody>
<?php if (!$categorias): ?><tr><td colspan="3" class="text-white-50">Nenhuma categoria cadastrada.</td></tr><?php endif; ?>
<?php foreach ($categorias as $categoria): ?><tr><td><?= (int)$categoria['id_categoria'] ?></td><td><?= e($categoria['nome']) ?></td><td><span class="badge <?= $categoria['ativo'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $categoria['ativo'] ? 'Ativa' : 'Inativa' ?></span></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>