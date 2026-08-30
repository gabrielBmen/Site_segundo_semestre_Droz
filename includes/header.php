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
require_once __DIR__ . '/auth.php';

$adminLogado = usuarioAdmin();
$quantidadeCarrinho = 0;
$perfilMenu = null;
$fotoMenuUrl = '';
$iniciaisMenu = 'DR';

if (usuarioLogado()) {
    require_once __DIR__ . '/../config/conexao.php';
    require_once __DIR__ . '/../models/UsuarioModel.php';

    $perfilMenu = (new UsuarioModel($pdo))->buscarPerfilPorId((int) (usuarioAtual()['id_usuario'] ?? 0));
    if ($perfilMenu) {
        $fotoMenu = (string) ($perfilMenu['foto_perfil'] ?? '');
        if (str_starts_with($fotoMenu, 'uploads/perfis/')) {
            $fotoMenuUrl = '/' . $fotoMenu;
        }

        $iniciaisMenu = '';
        $partesNomeMenu = preg_split('/\s+/', trim((string) $perfilMenu['nome'])) ?: [];
        foreach (array_slice($partesNomeMenu, 0, 2) as $parteNomeMenu) {
            $iniciaisMenu .= mb_strtoupper(mb_substr($parteNomeMenu, 0, 1));
        }
        $iniciaisMenu = $iniciaisMenu !== '' ? $iniciaisMenu : 'DR';
    }
}

if (usuarioLogado() && !$adminLogado && is_array($_SESSION['carrinho'] ?? null)) {
    $quantidadeCarrinho = array_sum(array_map('intval', $_SESSION['carrinho']));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> | DROZ Robótica</title>
    <meta name="description" content="<?= e($descricaoPagina) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=5">
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
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="/index.php">
            <img src="/assets/imagens/logo.png" alt="Logo DROZ" class="brand-logo">
            <span>DROZ Robótica</span>
        </a>

        <div class="d-flex align-items-center gap-2 order-lg-3 ms-auto ms-lg-3">
            <button class="account-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuConta" aria-controls="menuConta" aria-label="Abrir menu da conta">
                <span class="account-avatar account-avatar-small" aria-hidden="true">
                    <?php if ($fotoMenuUrl !== ''): ?>
                        <img src="<?= e($fotoMenuUrl) ?>" alt="">
                    <?php elseif (usuarioLogado()): ?>
                        <span><?= e($iniciaisMenu) ?></span>
                    <?php else: ?>
                        <i class="bi bi-person"></i>
                    <?php endif; ?>
                </span>
                <i class="bi bi-list fs-4" aria-hidden="true"></i>
            </button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse order-lg-2" id="menuPrincipal">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 py-3 py-lg-0">
                <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/index.php">Home</a></li>
                <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/sobre.php">Sobre</a></li>
                <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/servicos.php">Serviços</a></li>
                <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/produtos.php">Catálogo</a></li>
            </ul>
        </div>
    </div>
</nav>

<aside class="offcanvas offcanvas-end account-offcanvas" tabindex="-1" id="menuConta" aria-labelledby="menuContaTitulo">
    <div class="offcanvas-header">
        <div>
            <span class="account-eyebrow">DROZ Robótica</span>
            <h2 class="offcanvas-title h5 mb-0" id="menuContaTitulo"><?= usuarioLogado() ? 'Minha conta' : 'Acesso do usuário' ?></h2>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <?php if (usuarioLogado() && $perfilMenu): ?>
            <div class="account-menu-profile">
                <div class="account-avatar account-avatar-menu">
                    <?php if ($fotoMenuUrl !== ''): ?>
                        <img src="<?= e($fotoMenuUrl) ?>" alt="Foto de perfil de <?= e($perfilMenu['nome']) ?>">
                    <?php else: ?>
                        <span><?= e($iniciaisMenu) ?></span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <strong><?= e($perfilMenu['nome']) ?></strong>
                    <small><?= e($perfilMenu['email']) ?></small>
                    <span class="account-role"><i class="bi bi-shield-check"></i><?= $adminLogado ? 'Administrador' : 'Cliente' ?></span>
                </div>
            </div>

            <div class="account-menu-meta">
                <span>Conta criada em</span>
                <strong><?= e(date('d/m/Y', strtotime((string) $perfilMenu['data_criacao']))) ?></strong>
            </div>

            <?php if (!$adminLogado): ?>
                <div class="account-menu-stats">
                    <div><strong><?= (int) $perfilMenu['total_pedidos'] ?></strong><span>pedidos</span></div>
                    <div><strong><?= $quantidadeCarrinho ?></strong><span>itens no carrinho</span></div>
                </div>
            <?php endif; ?>

            <nav class="account-menu-links" aria-label="Opções da conta">
                <a href="/minha-conta.php"><i class="bi bi-person-circle"></i><span>Meus dados</span></a>
                <?php if ($adminLogado): ?>
                    <a href="/admin/"><i class="bi bi-speedometer2"></i><span>Dashboard administrativa</span></a>
                <?php else: ?>
                    <a href="/minha-conta.php#historico-pedidos"><i class="bi bi-clock-history"></i><span>Histórico de pedidos</span><b><?= (int) $perfilMenu['total_pedidos'] ?></b></a>
                    <a href="/carrinho.php"><i class="bi bi-cart3"></i><span>Meu carrinho</span><?php if ($quantidadeCarrinho > 0): ?><b><?= $quantidadeCarrinho ?></b><?php endif; ?></a>
                    <a href="/contato.php"><i class="bi bi-chat-dots"></i><span>Solicitar orçamento</span></a>
                <?php endif; ?>
            </nav>

            <div class="account-private-note mt-4">
                <i class="bi bi-shield-lock"></i>
                <div><strong>Seus dados são privados</strong><small>Somente a sua sessão autenticada acessa este perfil.</small></div>
            </div>

            <a href="/logout.php" class="btn btn-outline-light mt-auto"><i class="bi bi-box-arrow-right me-2"></i>Sair da conta</a>
        <?php else: ?>
            <div class="account-guest-state">
                <div class="account-avatar account-avatar-menu"><i class="bi bi-person"></i></div>
                <h3 class="h4 mt-4">Bem-vindo à DROZ</h3>
                <p class="text-white-50">Entre para acompanhar pedidos, gerenciar seus dados e solicitar atendimento.</p>
                <a href="/login.php" class="btn btn-primary w-100 mt-2">Entrar</a>
                <a href="/cadastro.php" class="btn btn-outline-light w-100 mt-2">Criar conta</a>
            </div>
        <?php endif; ?>
    </div>
</aside>
<main>
