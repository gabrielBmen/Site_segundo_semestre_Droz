"use strict";
const API_PRODUTOS = '/api/produtos.php';
const $ = (selector) => {
    return document.querySelector(selector);
};
const definirValor = (selector, valor) => {
    const campo = $(selector);
    if (campo instanceof HTMLInputElement || campo instanceof HTMLSelectElement || campo instanceof HTMLTextAreaElement) {
        campo.value = valor;
    }
};
const definirMarcado = (selector, marcado) => {
    const campo = $(selector);
    if (campo instanceof HTMLInputElement)
        campo.checked = marcado;
};
const formatarDinheiroProduto = (valor) => {
    if (valor === null || !Number.isFinite(valor))
        return '—';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
};
const escapeHtml = (valor) => {
    const div = document.createElement('div');
    div.textContent = String(valor ?? '');
    return div.innerHTML;
};
const caminhoImagem = (caminho) => {
    if (!caminho)
        return '';
    return caminho.startsWith('/') ? caminho : `/${caminho}`;
};
let categorias = [];
let modal = null;
const sincronizarCampoPreco = () => {
    const campoPreco = $('#produtoPreco');
    const permitePedido = $('#produtoPermitePedido');
    const ajuda = $('#produtoPrecoAjuda');
    if (!(campoPreco instanceof HTMLInputElement)
        || !(permitePedido instanceof HTMLInputElement))
        return;
    const pedidoOnline = permitePedido.checked;
    campoPreco.disabled = !pedidoOnline;
    campoPreco.required = pedidoOnline;
    if (!pedidoOnline) {
        campoPreco.value = '';
    }
    if (ajuda) {
        ajuda.textContent = pedidoOnline
            ? 'Informe o preço que será usado nos pedidos online.'
            : 'Produto sob orçamento: não possui preço cadastrado.';
    }
};
const mostrarAlertaProduto = (mensagem, tipo = 'success') => {
    const alerta = $('#produtoAlert');
    if (!alerta)
        return;
    alerta.textContent = mensagem;
    alerta.className = `alert alert-${tipo}`;
    window.setTimeout(() => {
        alerta.classList.add('d-none');
    }, 5000);
};
const preencherCategorias = () => {
    const filtro = $('#filtroCategoria');
    const select = $('#produtoCategoria');
    const opcoes = categorias.map((categoria) => (`<option value="${categoria.id_categoria}">${escapeHtml(categoria.nome)}</option>`)).join('');
    if (filtro instanceof HTMLSelectElement) {
        filtro.innerHTML = '<option value="">Todas as categorias</option>' + opcoes;
    }
    if (select instanceof HTMLSelectElement) {
        select.innerHTML = '<option value="">Selecione...</option>' + opcoes;
    }
};
const carregarCategorias = async () => {
    const resultado = await window.drozApi.getJson(`${API_PRODUTOS}?acao=categorias`);
    if (!resultado.sucesso || !resultado.dados) {
        throw new Error(resultado.mensagem ?? 'Não foi possível carregar as categorias.');
    }
    categorias = Array.isArray(resultado.dados) ? resultado.dados : [];
    preencherCategorias();
};
const carregarProdutos = async () => {
    const tbody = $('#produtosTableBody');
    const count = $('#produtoCount');
    if (!tbody || !count)
        return;
    const buscaCampo = $('#buscaProduto');
    const categoriaCampo = $('#filtroCategoria');
    const busca = buscaCampo instanceof HTMLInputElement ? buscaCampo.value.trim() : '';
    const idCategoria = categoriaCampo instanceof HTMLSelectElement ? categoriaCampo.value : '';
    const params = new URLSearchParams({ acao: 'listar' });
    if (busca)
        params.set('busca', busca);
    if (idCategoria)
        params.set('id_categoria', idCategoria);
    tbody.innerHTML = '<tr><td colspan="9" class="text-white-50">Carregando produtos...</td></tr>';
    try {
        const resultado = await window.drozApi.getJson(`${API_PRODUTOS}?${params.toString()}`);
        if (!resultado.sucesso || !resultado.dados) {
            throw new Error(resultado.mensagem ?? 'Não foi possível carregar os produtos.');
        }
        const produtos = Array.isArray(resultado.dados) ? resultado.dados : [];
        count.textContent = String(produtos.length);
        if (produtos.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="9" class="text-white-50">Nenhum produto encontrado.</td></tr>';
            return;
        }
        tbody.innerHTML = produtos.map((produto) => {
            const imagem = caminhoImagem(produto.imagem);
            const miniatura = imagem
                ? `<img src="${escapeHtml(imagem)}" alt="" width="58" height="58" class="rounded-3 object-fit-cover border border-secondary">`
                : '<div class="d-flex align-items-center justify-content-center rounded-3 bg-secondary-subtle text-dark" style="width:58px;height:58px;"><i class="bi bi-image"></i></div>';
            return `
                <tr>
                    <td>${miniatura}</td>
                    <td>#${produto.id_produto}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(produto.nome)}</div>
                        <div class="small text-white-50">${escapeHtml(produto.slug)}</div>
                    </td>
                    <td>${escapeHtml(produto.categoria)}</td>
                        <td>${produto.permite_pedido ? formatarDinheiroProduto(produto.preco) : 'Sob orçamento'}</td>
                        <td>${produto.estoque}</td>
                        <td>
                            <span class="badge ${produto.permite_pedido ? 'text-bg-info' : 'text-bg-warning'}">
                                ${produto.permite_pedido ? 'Pedido online' : 'Sob orçamento'}
                            </span>
                        </td>
                        <td>
                        <span class="badge ${produto.ativo ? 'text-bg-success' : 'text-bg-secondary'}">
                            ${produto.ativo ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-outline-light me-1" type="button" data-editar-produto="${produto.id_produto}" title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-excluir-produto="${produto.id_produto}" data-nome-produto="${escapeHtml(produto.nome)}" title="Excluir">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
    catch (erro) {
        tbody.innerHTML =
            '<tr><td colspan="9" class="text-danger">Não foi possível carregar os produtos.</td></tr>';
        mostrarAlertaProduto(erro instanceof Error ? erro.message : 'Erro ao carregar produtos.', 'danger');
    }
};
const limparForm = () => {
    const form = $('#produtoForm');
    if (!(form instanceof HTMLFormElement))
        return;
    form.reset();
    definirValor('#produtoId', '');
    definirMarcado('#produtoAtivo', true);
    definirMarcado('#produtoPermitePedido', false);
    definirValor('#produtoEstoque', '0');
    sincronizarCampoPreco();
    const titulo = $('#produtoModalLabel');
    if (titulo)
        titulo.textContent = 'Novo produto';
    const imagensSecao = $('#imagensAtuaisSecao');
    if (imagensSecao)
        imagensSecao.classList.add('d-none');
    const atuais = $('#imagensAtuais');
    if (atuais)
        atuais.innerHTML = '';
    const preview = $('#previewNovasImagens');
    if (preview)
        preview.innerHTML = '';
};
const preencherForm = (produto) => {
    definirValor('#produtoId', String(produto.id_produto));
    definirValor('#produtoNome', produto.nome);
    definirValor('#produtoCategoria', String(produto.id_categoria));
    definirValor('#produtoPreco', produto.preco === null ? '' : produto.preco.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
    definirValor('#produtoEstoque', String(produto.estoque));
    // Corrigido: Number("0") === 0 e Number("1") === 1.
    // Boolean("0") seria true, causando o switch sempre ligado ao editar.
    definirMarcado('#produtoAtivo', produto.ativo);
    definirMarcado('#produtoPermitePedido', produto.permite_pedido);
    sincronizarCampoPreco();
    definirValor('#produtoDescricao', produto.descricao ?? '');
    const titulo = $('#produtoModalLabel');
    if (titulo)
        titulo.textContent = `Editar produto #${produto.id_produto}`;
    renderImagensAtuais(produto.imagens ?? []);
};
const renderImagensAtuais = (imagens) => {
    const secao = $('#imagensAtuaisSecao');
    const container = $('#imagensAtuais');
    if (!secao || !container)
        return;
    if (imagens.length === 0) {
        secao.classList.add('d-none');
        container.innerHTML = '';
        return;
    }
    secao.classList.remove('d-none');
    container.innerHTML = imagens.map((imagem) => {
        const src = caminhoImagem(imagem.caminho);
        return `
            <div class="col-md-6 col-xl-4">
                <div class="border border-secondary rounded-4 p-2 h-100 bg-black bg-opacity-25">
                    <img src="${escapeHtml(src)}" alt="" class="w-100 rounded-3 mb-2" style="height:150px;object-fit:cover;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <label class="form-check mb-0 small">
                            <input class="form-check-input imagem-principal-radio" type="radio" name="imagem_principal_id" value="${imagem.id_imagem}" ${imagem.principal ? 'checked' : ''}>
                            <span class="form-check-label">Principal</span>
                        </label>
                        <label class="form-check mb-0 small text-danger">
                            <input class="form-check-input imagem-remover-check" type="checkbox" name="remover_imagens[]" value="${imagem.id_imagem}">
                            <span class="form-check-label">Remover</span>
                        </label>
                    </div>
                </div>
            </div>
        `;
    }).join('');
};
const previewNovasImagens = () => {
    const input = $('#produtoImagens');
    const container = $('#previewNovasImagens');
    if (!(input instanceof HTMLInputElement) || !container)
        return;
    container.innerHTML = '';
    Array.from(input.files ?? []).forEach((arquivo) => {
        const url = URL.createObjectURL(arquivo);
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3';
        col.innerHTML = `
            <div class="border border-secondary rounded-3 p-2">
                <img src="${url}" alt="${escapeHtml(arquivo.name)}" class="w-100 rounded-2" style="height:110px;object-fit:cover;">
                <div class="small text-white-50 text-truncate mt-1" title="${escapeHtml(arquivo.name)}">${escapeHtml(arquivo.name)}</div>
            </div>
        `;
        container.appendChild(col);
    });
};
const abrirNovo = () => {
    limparForm();
    modal?.show();
};
const abrirEditar = async (id) => {
    try {
        const resultado = await window.drozApi.getJson(`${API_PRODUTOS}?acao=buscar&id=${id}`);
        if (!resultado.sucesso || !resultado.dados) {
            throw new Error(resultado.mensagem ?? 'Produto não encontrado.');
        }
        preencherForm(resultado.dados);
        modal?.show();
    }
    catch (erro) {
        mostrarAlertaProduto(erro instanceof Error ? erro.message : 'Não foi possível carregar o produto.', 'danger');
    }
};
const salvarProduto = async (event) => {
    event.preventDefault();
    const form = $('#produtoForm');
    const botao = $('#btnSalvarProduto');
    const produtoAtivo = $('#produtoAtivo');
    const produtoPermitePedido = $('#produtoPermitePedido');
    if (!(form instanceof HTMLFormElement)
        || !(botao instanceof HTMLButtonElement)
        || !(produtoAtivo instanceof HTMLInputElement)
        || !(produtoPermitePedido instanceof HTMLInputElement))
        return;
    const formData = new FormData(form);
    // Checkbox desmarcado não entra no FormData automaticamente.
    // Forçamos sempre 1 ou 0 para o PHP receber o estado correto.
    formData.set('ativo', produtoAtivo.checked ? '1' : '0');
    formData.set('permite_pedido', produtoPermitePedido.checked ? '1' : '0');
    botao.disabled = true;
    botao.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';
    try {
        const resultado = await window.drozApi.postFormData(API_PRODUTOS, formData);
        if (!resultado.sucesso) {
            throw new Error(resultado.mensagem ?? 'Não foi possível salvar o produto.');
        }
        modal?.hide();
        mostrarAlertaProduto(resultado.mensagem ?? 'Produto salvo com sucesso.');
        await carregarProdutos();
    }
    catch (erro) {
        mostrarAlertaProduto(erro instanceof Error ? erro.message : 'Erro ao salvar produto.', 'danger');
    }
    finally {
        botao.disabled = false;
        botao.innerHTML =
            '<i class="bi bi-check2-circle me-1"></i> Salvar produto';
    }
};
const excluirProduto = async (id, nome) => {
    const confirmou = window.confirm(`Deseja realmente excluir o produto "${nome}"?\n\n` +
        'Se ele já possuir histórico de pedidos, o sistema bloqueará a exclusão e você poderá desativá-lo.');
    if (!confirmou)
        return;
    const formData = new FormData();
    formData.set('acao', 'excluir');
    formData.set('id_produto', String(id));
    const csrf = $('#produtoCsrfToken');
    if (!(csrf instanceof HTMLInputElement) || csrf.value === '') {
        mostrarAlertaProduto('Sua sessão expirou. Atualize a página e tente novamente.', 'danger');
        return;
    }
    formData.set('csrf_token', csrf.value);
    try {
        const resultado = await window.drozApi.postFormData(API_PRODUTOS, formData);
        if (!resultado.sucesso) {
            throw new Error(resultado.mensagem ?? 'Não foi possível excluir o produto.');
        }
        mostrarAlertaProduto(resultado.mensagem ?? 'Produto excluído com sucesso.');
        await carregarProdutos();
    }
    catch (erro) {
        mostrarAlertaProduto(erro instanceof Error ? erro.message : 'Erro ao excluir produto.', 'danger');
    }
};
const ligarEventos = () => {
    $('#btnNovoProduto')?.addEventListener('click', abrirNovo);
    $('#produtoForm')?.addEventListener('submit', (event) => {
        void salvarProduto(event);
    });
    $('#produtoImagens')?.addEventListener('change', previewNovasImagens);
    $('#produtoPermitePedido')?.addEventListener('change', sincronizarCampoPreco);
    $('#buscaProduto')?.addEventListener('input', () => {
        window.clearTimeout(window.produtoSearchTimer);
        window.produtoSearchTimer = window.setTimeout(() => {
            void carregarProdutos();
        }, 350);
    });
    $('#filtroCategoria')?.addEventListener('change', () => {
        void carregarProdutos();
    });
    $('#produtosTableBody')?.addEventListener('click', (event) => {
        const alvo = event.target;
        if (!(alvo instanceof Element))
            return;
        const editar = alvo.closest('[data-editar-produto]');
        const excluir = alvo.closest('[data-excluir-produto]');
        if (editar) {
            void abrirEditar(Number(editar.dataset.editarProduto));
            return;
        }
        if (excluir) {
            void excluirProduto(Number(excluir.dataset.excluirProduto), excluir.dataset.nomeProduto ?? 'este produto');
        }
    });
};
document.addEventListener('DOMContentLoaded', async () => {
    if (!window.drozApi)
        return;
    try {
        const ModalClass = window.bootstrap?.Modal;
        if (ModalClass) {
            const elemento = $('#produtoModal');
            if (elemento instanceof HTMLElement) {
                modal = new ModalClass(elemento);
            }
        }
        ligarEventos();
        await carregarCategorias();
        await carregarProdutos();
    }
    catch (erro) {
        mostrarAlertaProduto(erro instanceof Error
            ? erro.message
            : 'Não foi possível iniciar o gerenciamento de produtos.', 'danger');
    }
});
//# sourceMappingURL=admin-produtos.js.map