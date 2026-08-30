"use strict";
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formLogin');
    const mensagem = document.getElementById('mensagemLogin');
    if (!(form instanceof HTMLFormElement) || !mensagem || !window.drozApi)
        return;
    const mostrarMensagem = (texto, tipo = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const emailCampo = document.getElementById('email');
        const senhaCampo = document.getElementById('senha');
        const redirectCampo = document.getElementById('redirect');
        if (!(emailCampo instanceof HTMLInputElement)
            || !(senhaCampo instanceof HTMLInputElement)
            || !(redirectCampo instanceof HTMLInputElement))
            return;
        try {
            const resultado = await window.drozApi.postJson('/api/login.php', {
                email: emailCampo.value.trim(),
                senha: senhaCampo.value,
                redirect: redirectCampo.value
            });
            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Não foi possível realizar o login.');
                return;
            }
            mostrarMensagem(resultado.mensagem ?? 'Login realizado com sucesso.', 'success');
            window.location.href = resultado.redirect ?? 'index.php';
        }
        catch (erro) {
            console.error('Erro no login:', erro);
            mostrarMensagem(erro instanceof Error ? erro.message : 'Não foi possível concluir o login.');
        }
    });
});
//# sourceMappingURL=login.js.map