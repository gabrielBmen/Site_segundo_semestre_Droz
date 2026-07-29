<?php
if (!isset($tituloPagina)) {
    $tituloPagina = 'DROZ Robótica';
}
if (!isset($descricaoPagina)) {
    $descricaoPagina = 'Soluções em automação industrial, robótica e células robotizadas de solda.';
}
if (!isset($paginaAtiva)) {
    $paginaAtiva = 'home';
}
if (!function_exists('e')) {
    require_once __DIR__ . '/funcoes.php';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> | DROZ Robótica</title>
    <meta name="description" content="<?= e($descricaoPagina) ?>">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="container d-flex flex-wrap justify-content-between align-items-center py-2">
        <div class="small text-white-50">Campo Mourão - PR • Indústria brasileira de células robotizadas de solda</div>
        <div class="small">
            <a class="text-white-50 text-decoration-none me-3" href="mailto:drozrobotica@drozrobotica.com"><i class="bi bi-envelope me-1"></i>drozrobotica@drozrobotica.com</a>
            <a class="text-white-50 text-decoration-none" href="https://www.linkedin.com/company/droz-rob%C3%B3tica" target="_blank" rel="noreferrer"><i class="bi bi-linkedin me-1"></i>LinkedIn</a>
        </div>
    </div>
</header>

<nav class="navbar navbar-expand-lg navbar-dark main-nav sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
                <img src="assets/logo.png" alt="Logo DROZ" style="height: 38px; width: auto; border-radius: 8px;">
                <span>DROZ Robótica</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link <?= $paginaAtiva === 'home' ? 'active' : '' ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $paginaAtiva === 'sobre' ? 'active' : '' ?>" href="sobre.php">Sobre</a></li>
                <li class="nav-item"><a class="nav-link <?= $paginaAtiva === 'produtos' ? 'active' : '' ?>" href="produtos.php">Produtos</a></li>
                <li class="nav-item"><a class="nav-link <?= $paginaAtiva === 'servicos' ? 'active' : '' ?>" href="servicos.php">Serviços</a></li>
                <li class="nav-item"><a class="nav-link <?= $paginaAtiva === 'contato' ? 'active' : '' ?>" href="contato.php">Contato</a></li>
            </ul>
            <div class="ms-lg-3 d-flex gap-2 mt-3 mt-lg-0">
                <a href="produtos.php" class="btn btn-outline-light btn-sm">Catálogo</a>
                <a href="contato.php" class="btn btn-primary btn-sm">Solicitar orçamento</a>
            </div>
        </div>
    </div>
</nav>
<main>
