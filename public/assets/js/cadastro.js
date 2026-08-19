document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCadastro');
    const mensagem = document.getElementById('mensagemCadastro');

    if (!form || !mensagem) return;

    const mostrarMensagem = (texto, tipo = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const dados = {
            nome: document.getElementById('nome').value.trim(),
            email: document.getElementById('emailCadastro').value.trim(),
            telefone: document.getElementById('telefone').value.trim(),
            senha: document.getElementById('senhaCadastro').value,
            confirmar_senha: document.getElementById('confirmarSenha').value
        };

        try {
            const resposta = await fetch('/api/cadastro.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
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

            mostrarMensagem(`${resultado.mensagem} Redirecionando para o login...`, 'success');

            const redirect = document.getElementById('redirectCadastro').value;
            const url = `login.php?redirect=${encodeURIComponent(redirect)}`;

            setTimeout(() => {
                window.location.href = url;
            }, 800);
        } catch (erro) {
            console.error('Erro no cadastro:', erro);
            mostrarMensagem('Não foi possível concluir o cadastro. Verifique se o servidor e o banco de dados estão ativos.');
        }
    });
});
