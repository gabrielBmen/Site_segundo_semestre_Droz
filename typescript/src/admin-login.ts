document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formAdminLogin') as HTMLFormElement | null;
    const mensagem = document.getElementById('mensagemAdminLogin') as HTMLElement | null;

    if (!form || !mensagem || !window.drozApi) return;

    const mostrarMensagem = (texto: string, tipo: 'danger' | 'success' = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = (document.getElementById('adminEmail') as HTMLInputElement).value.trim();
        const senha = (document.getElementById('adminSenha') as HTMLInputElement).value;

        try {
            const resultado = await window.drozApi.postJson<{ [key: string]: unknown }>('/api/admin-login.php', {
                email,
                senha
            });

            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Login administrativo inválido.');
                return;
            }

            mostrarMensagem(resultado.mensagem ?? 'Login realizado com sucesso.', 'success');
            window.location.href = String(resultado.redirect ?? '/admin/');
        } catch (erro) {
            console.error('Erro no login administrativo:', erro);
            mostrarMensagem(erro instanceof Error ? erro.message : 'Não foi possível concluir o login.');
        }
    });
});
