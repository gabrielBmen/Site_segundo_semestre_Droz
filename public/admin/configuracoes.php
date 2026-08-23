<?php
$adminPagina = 'Configurações';
include __DIR__ . '/includes/header.php';
?>
<div class="dashboard-card">
    <h2 class="h5">Ambiente da aplicação</h2>
    <p class="text-white-50">Painel administrativo protegido por sessão e perfil <code>admin</code>.</p>
    <div class="row g-3">
        <div class="col-md-4"><div class="admin-info-box"><div class="text-white-50 small">Banco</div><strong>MariaDB / MySQL</strong></div></div>
        <div class="col-md-4"><div class="admin-info-box"><div class="text-white-50 small">Servidor</div><strong>Apache / XAMPP</strong></div></div>
        <div class="col-md-4"><div class="admin-info-box"><div class="text-white-50 small">Frontend</div><strong>PHP + Bootstrap + TypeScript</strong></div></div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>