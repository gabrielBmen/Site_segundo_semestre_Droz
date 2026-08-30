<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/auth.php';

if (usuarioLogado()) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Criar conta';
$descricaoPagina = 'Crie sua conta na DROZ Robótica.';
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
                        <i class="bi bi-person-plus me-2"></i>Novo cliente
                    </span>

                    <h1 class="section-title mb-2">Criar conta</h1>
                    <p class="section-subtitle mb-4">
                        Com a conta, você poderá comprar produtos, acompanhar o histórico dos pedidos e gerenciar seus dados com segurança.
                    </p>

                    <div id="mensagemCadastro" class="alert d-none" role="alert"></div>

                    <form id="formCadastro" novalidate>
                        <input type="hidden" id="redirectCadastro" value="<?= e($redirect) ?>">

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" autocomplete="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="emailCadastro" class="form-label">E-mail</label>
                            <input type="email" id="emailCadastro" name="email" class="form-control" autocomplete="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" autocomplete="tel" maxlength="30" placeholder="(44) 99999-9999" required>
                            <div class="form-text text-white-50">Você poderá alterar este número posteriormente em Minha conta.</div>
                        </div>

                        <div class="mb-3">
                            <label for="senhaCadastro" class="form-label">Senha</label>
                            <input type="password" id="senhaCadastro" name="senha" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>

                        <div class="mb-4">
                            <label for="confirmarSenha" class="form-label">Confirmar senha</label>
                            <input type="password" id="confirmarSenha" name="confirmar_senha" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            Criar conta
                        </button>
                        <p class="small text-white-50 text-center mt-3 mb-0">
                            Depois do cadastro, você também poderá adicionar uma foto ao perfil.
                        </p>
                    </form>

                    <p class="text-center text-white-50 mt-4 mb-0">
                        Já possui conta?
                        <a href="login.php?redirect=<?= rawurlencode($redirect) ?>" class="text-white fw-semibold">Entrar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/api.js?v=1"></script>
<script src="assets/js/cadastro.js?v=3"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
