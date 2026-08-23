document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formLogin') as HTMLFormElement | null;
    const mensagem = document.getElementById('mensagemLogin') as HTMLElement | null;

    if (!form || !mensagem || !window.drozApi) return;

    const mostrarMensagem = (texto: string, tipo: 'danger' | 'success' = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = (document.getElementById('email') as HTMLInputElement).value.trim();
        const senha = (document.getElementById('senha') as HTMLInputElement).value;
        const redirect = (document.getElementById('redirect') as HTMLInputElement).value;

        try {
            const resultado = await window.drozApi.postJson<{ [key: string]: unknown }>('/api/login.php', {
                email,
                senha,
                redirect
            });

            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Não foi possível realizar o login.');
                return;
            }

            mostrarMensagem(resultado.mensagem ?? 'Login realizado com sucesso.', 'success');
            window.location.href = String(resultado.redirect ?? 'index.php');
        } catch (erro) {
            console.error('Erro no login:', erro);
            mostrarMensagem(erro instanceof Error ? erro.message : 'Não foi possível concluir o login.');
        }
    });
});
