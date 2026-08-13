<?php

require_once __DIR__ . '/../includes/auth.php';

if (usuarioLogado()) {
    $nome = usuarioAtual()['nome'] ?? 'usuário';
}

$tituloPagina = 'Sair';
$descricaoPagina = 'Encerrar sessão.';
$paginaAtiva = '';

include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="glass-card rounded-4 p-5 text-center">
                    <h1 class="section-title mb-3">Encerrar sessão</h1>
                    <p class="text-white-50 mb-4">Tem certeza que deseja sair?</p>
                    <button id="btnLogout" class="btn btn-primary btn-lg">Sair da conta</button>
                    <a href="index.php" class="btn btn-outline-light btn-lg ms-2">Voltar</a>
                    <div id="mensagemLogout" class="alert d-none mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('btnLogout')?.addEventListener('click', async () => {
    const mensagem = document.getElementById('mensagemLogout');

    try {
        const resposta = await fetch('../api/logout.php', { method: 'POST' });
        const resultado = await resposta.json();

        mensagem.className = 'alert alert-success mt-4';
        mensagem.textContent = resultado.mensagem;

        setTimeout(() => {
            window.location.href = resultado.redirect;
        }, 500);
    } catch (erro) {
        mensagem.className = 'alert alert-danger mt-4';
        mensagem.textContent = 'Não foi possível encerrar a sessão.';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
