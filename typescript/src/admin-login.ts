document.addEventListener('DOMContentLoaded', (): void => {
    const form = document.getElementById('formAdminLogin');
    const mensagem = document.getElementById('mensagemAdminLogin');

    if (!(form instanceof HTMLFormElement) || !mensagem || !window.drozApi) return;

    const mostrarMensagem = (texto: string, tipo: 'danger' | 'success' = 'danger'): void => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };

    form.addEventListener('submit', async (event): Promise<void> => {
        event.preventDefault();

        const emailCampo = document.getElementById('adminEmail');
        const senhaCampo = document.getElementById('adminSenha');
        if (!(emailCampo instanceof HTMLInputElement) || !(senhaCampo instanceof HTMLInputElement)) return;

        try {
            const resultado = await window.drozApi.postJson<unknown>('/api/admin-login.php', {
                email: emailCampo.value.trim(),
                senha: senhaCampo.value
            });

            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Login administrativo inválido.');
                return;
            }

            mostrarMensagem(resultado.mensagem ?? 'Login realizado com sucesso.', 'success');
            window.location.href = resultado.redirect ?? '/admin/';
        } catch (erro) {
            console.error('Erro no login administrativo:', erro);
            mostrarMensagem(erro instanceof Error ? erro.message : 'Não foi possível concluir o login.');
        }
    });
});
