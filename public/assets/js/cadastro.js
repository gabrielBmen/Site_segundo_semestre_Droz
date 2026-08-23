"use strict";
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCadastro');
    const mensagem = document.getElementById('mensagemCadastro');
    if (!form || !mensagem || !window.drozApi)
        return;
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
            const resultado = await window.drozApi.postJson('/api/cadastro.php', dados);
            if (!resultado.sucesso) {
                mostrarMensagem(resultado.mensagem ?? 'Não foi possível concluir o cadastro.');
                return;
            }
            mostrarMensagem(`${resultado.mensagem ?? 'Cadastro realizado com sucesso.'} Redirecionando para o login...`, 'success');
            const redirect = document.getElementById('redirectCadastro')?.value ?? 'index.php';
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