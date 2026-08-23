<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/auth.php';

if (usuarioAdmin()) {
    header('Location: /admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login administrativo | DROZ Robótica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/admin/admin.css?v=1">
</head>
<body class="admin-login-body">
<div class="admin-login-card">
    <div class="text-center mb-4">
        <img src="/assets/imagens/logo.png" class="admin-login-logo" alt="DROZ Robótica">
        <div class="text-uppercase small text-white-50 mt-3">Área restrita</div>
        <h1 class="h3 mt-1">Login administrativo</h1>
        <p class="text-white-50">Acesse a dashboard e os dados de gestão.</p>
    </div>

    <div id="mensagemAdminLogin" class="alert d-none" role="alert"></div>

    <form id="formAdminLogin" novalidate>
        <div class="mb-3">
            <label for="adminEmail" class="form-label">E-mail</label>
            <input type="email" id="adminEmail" class="form-control" autocomplete="username" required>
        </div>
        <div class="mb-4">
            <label for="adminSenha" class="form-label">Senha</label>
            <input type="password" id="adminSenha" class="form-control" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no painel
        </button>
    </form>

    <div class="text-center mt-4">
        <a class="text-white-50 small" href="/login.php">Voltar para o login do site</a>
    </div>
</div>
<script src="/assets/js/api.js?v=1"></script>
<script src="/assets/js/admin-login.js?v=1"></script>
</body>
</html>
