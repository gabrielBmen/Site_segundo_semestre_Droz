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
            const resposta = await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, senha, redirect })
            });

            const tipoConteudo = resposta.headers.get('content-type') || '';

            if (!tipoConteudo.includes('application/json')) {
                throw new Error(`Resposta inválida do servidor (HTTP ${resposta.status}).`);
            }

            const resultado = await resposta.json();

            if (!resposta.ok || !resultado.sucesso) {
                mostrarMensagem(resultado.mensagem);
                return;
            }

            mostrarMensagem(resultado.mensagem, 'success');

            window.location.href = resultado.redirect;
        } catch (erro) {
            console.error('Erro no login:', erro);
            mostrarMensagem('Não foi possível concluir o login. Verifique se o servidor e o banco de dados estão ativos.');
        }
    });
});
