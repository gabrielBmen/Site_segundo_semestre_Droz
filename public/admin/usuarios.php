<?php
$adminPagina = 'Usuários';
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../config/conexao.php';
$stmt = $pdo->query("SELECT id_usuario, nome, email, tipo, ativo, data_criacao FROM usuarios ORDER BY id_usuario DESC");
$usuarios = $stmt->fetchAll();
?>
<div class="dashboard-card"><div class="table-responsive"><table class="table table-dark align-middle mb-0"><thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Status</th><th>Criado em</th></tr></thead><tbody>
<?php if (!$usuarios): ?><tr><td colspan="6" class="text-white-50">Nenhum usuário cadastrado.</td></tr><?php endif; ?>
<?php foreach ($usuarios as $usuario): ?><tr><td><?= (int)$usuario['id_usuario'] ?></td><td><?= e($usuario['nome']) ?></td><td><?= e($usuario['email']) ?></td><td><span class="badge <?= $usuario['tipo'] === 'admin' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= e($usuario['tipo']) ?></span></td><td><?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?></td><td><?= e(date('d/m/Y', strtotime($usuario['data_criacao']))) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>