<?php
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/auth.php';
exigirAdmin();

$adminPagina = $adminPagina ?? 'Dashboard';
$usuario = usuarioAtual() ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPagina) ?> | Administração DROZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/admin/admin.css?v=1">
</head>
<body class="admin-body">
<div class="admin-shell">
    <?php include __DIR__ . '/menu.php'; ?>
    <main class="admin-main">
        <header class="admin-topbar d-flex justify-content-between align-items-center gap-3">
            <div>
                <div class="text-uppercase small text-white-50">Painel administrativo</div>
                <h1 class="h3 mb-0"><?= e($adminPagina) ?></h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-success"><i class="bi bi-shield-check me-1"></i>Admin</span>
                <span class="small text-white-50 d-none d-md-inline"><?= e($usuario['nome'] ?? 'Administrador') ?></span>
                <a href="/logout.php?redirect=/admin/" class="btn btn-outline-light btn-sm">Sair</a>
            </div>
        </header>
        <div class="container-fluid py-4">
