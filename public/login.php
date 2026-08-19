<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/auth.php';

if (usuarioLogado()) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Login';
$descricaoPagina = 'Acesse sua conta para consultar preços, detalhes das máquinas e enviar mensagens à DROZ Robótica.';
$paginaAtiva = '';

$redirect = (string) ($_GET['redirect'] ?? '');
if (!urlInternaValida($redirect)) {
    $redirect = 'index.php';
}

include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="glass-card rounded-4 p-4 p-lg-5">
                    <span class="badge-soft mb-3 d-inline-flex">
                        <i class="bi bi-shield-lock me-2"></i>Área de acesso
                    </span>

                    <h1 class="section-title mb-2">Entrar na sua conta</h1>

                    <p class="section-subtitle mb-4">
                        O login é necessário para consultar preços, ver detalhes completos das máquinas e enviar solicitações de contato.
                    </p>

                    <div id="mensagemLogin" class="alert d-none" role="alert"></div>

                    <form id="formLogin" novalidate>
                        <input type="hidden" id="redirect" value="<?= e($redirect) ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" autocomplete="email" required>
                        </div>

                        <div class="mb-4">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" id="senha" name="senha" class="form-control" autocomplete="current-password" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            Entrar
                        </button>
                    </form>

                    <p class="text-center text-white-50 mt-4 mb-0">
                        Ainda não possui conta?
                        <a href="cadastro.php" class="text-white fw-semibold">Criar conta</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/login.js?v=2"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
