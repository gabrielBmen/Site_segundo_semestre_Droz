<?php
$adminPagina = 'Produtos';
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
$stmt = $pdo->query("SELECT p.id_produto, p.nome, c.nome AS categoria, p.preco, p.estoque, p.ativo FROM produtos p INNER JOIN categorias c ON c.id_categoria = p.id_categoria ORDER BY p.id_produto DESC");
$produtos = $stmt->fetchAll();
?>
<div class="dashboard-card">
    <div class="table-responsive">
        <table class="table table-dark align-middle mb-0"><thead><tr><th>ID</th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th></tr></thead><tbody>
        <?php if (!$produtos): ?><tr><td colspan="6" class="text-white-50">Nenhum produto cadastrado.</td></tr><?php endif; ?>
        <?php foreach ($produtos as $produto): ?><tr><td><?= (int)$produto['id_produto'] ?></td><td><?= e($produto['nome']) ?></td><td><?= e($produto['categoria']) ?></td><td><?= e(number_format((float)$produto['preco'],2,',','.')) ?></td><td><?= (int)$produto['estoque'] ?></td><td><span class="badge <?= $produto['ativo'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $produto['ativo'] ? 'Ativo' : 'Inativo' ?></span></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>