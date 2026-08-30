"use strict";
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCadastro');
    const mensagem = document.getElementById('mensagemCadastro');
    if (!(form instanceof HTMLFormElement) || !mensagem || !window.drozApi)
        return;
    const mostrarMensagem = (texto, tipo = 'danger') => {
        mensagem.textContent = texto;
        mensagem.className = `alert alert-${tipo}`;
    };
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const nome = document.getElementById('nome');
        const email = document.getElementById('emailCadastro');
        const telefone = document.getElementById('telefone');
        const senha = document.getElementById('senhaCadastro');
        const confirmarSenha = document.getElementById('confirmarSenha');
        if (!(nome instanceof HTMLInputElement)
            || !(email instanceof HTMLInputElement)
            || !(telefone instanceof HTMLInputElement)
            || !(senha instanceof HTMLInputElement)
            || !(confirmarSenha instanceof HTMLInputElement))
            return;
        const dados = {
            nome: nome.value.trim(),
            email: email.value.trim(),
            telefone: telefone.value.trim(),
            senha: senha.value,
            confirmar_senha: confirmarSenha.value
        };
        try {
            const resultado = await window.drozApi.postJson('/api/cadastro.php', dados);
            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Não foi possível concluir o cadastro.');
                return;
            }
            mostrarMensagem(`${resultado.mensagem ?? 'Cadastro realizado com sucesso.'} Redirecionando para o login...`, 'success');
            const redirectCampo = document.getElementById('redirectCadastro');
            const redirect = redirectCampo instanceof HTMLInputElement ? redirectCampo.value : 'index.php';
            const url = `login.php?redirect=${encodeURIComponent(redirect)}`;
            window.setTimeout(() => {
                window.location.href = url;
            }, 800);
        }
        catch (erro) {
            console.error('Erro no cadastro:', erro);
            mostrarMensagem(erro instanceof Error ? erro.message : 'Não foi possível concluir o cadastro.');
        }
    });
});
//# sourceMappingURL=cadastro.js.map