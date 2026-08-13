document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formLogin');
    const mensagem = document.getElementById('mensagemLogin');

    if (!form || !mensagem) return;

    const mostrarMensagem = (texto, tipo = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;
        const redirect = document.getElementById('redirect').value;

        try {
            const resposta = await fetch('../api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, senha, redirect })
            });

            const resultado = await resposta.json();

            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem);
                return;
            }

            mostrarMensagem(resultado.mensagem, 'success');

            window.location.href = resultado.redirect;
        } catch (erro) {
            console.error(erro);
            mostrarMensagem('Não foi possível concluir o login.');
        }
    });
});
