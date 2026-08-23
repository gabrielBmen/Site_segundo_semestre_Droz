document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCadastro') as HTMLFormElement | null;
    const mensagem = document.getElementById('mensagemCadastro') as HTMLElement | null;

    if (!form || !mensagem || !window.drozApi) return;

    const mostrarMensagem = (texto: string, tipo: 'danger' | 'success' = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const dados = {
            nome: (document.getElementById('nome') as HTMLInputElement).value.trim(),
            email: (document.getElementById('emailCadastro') as HTMLInputElement).value.trim(),
            telefone: (document.getElementById('telefone') as HTMLInputElement).value.trim(),
            senha: (document.getElementById('senhaCadastro') as HTMLInputElement).value,
            confirmar_senha: (document.getElementById('confirmarSenha') as HTMLInputElement).value
        };

        try {
            const resultado = await window.drozApi.postJson<{ [key: string]: unknown }>('/api/cadastro.php', dados);

            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Não foi possível concluir o cadastro.');
                return;
            }

            mostrarMensagem(`${resultado.mensagem ?? 'Cadastro realizado com sucesso.'} Redirecionando para o login...`, 'success');
            const redirect = (document.getElementById('redirectCadastro') as HTMLInputElement | null)?.value ?? 'index.php';
            const url = `login.php?redirect=${encodeURIComponent(redirect)}`;

            window.setTimeout(() => {
                window.location.href = url;
            }, 800);
        } catch (erro) {
            console.error('Erro no cadastro:', erro);
            mostrarMensagem(erro instanceof Error ? erro.message : 'Não foi possível concluir o cadastro.');
        }
    });
});
