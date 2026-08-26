interface ProdutoImagem {
    id_imagem: number;
    id_produto: number;
    caminho: string;
    principal: boolean;
    data_criacao?: string;
}

interface ProdutoAdmin {
    id_produto: number;
    id_categoria: number;
    nome: string;
    slug: string;
    descricao: string | null;
    preco: number;
    estoque: number;
    ativo: boolean;
    categoria: string;
    imagem: string | null;
    imagens?: ProdutoImagem[];
}

interface CategoriaAdmin {
    id_categoria: number;
    nome: string;
    ativo: boolean;
}

const API_PRODUTOS = '/api/produtos.php';

const $ = <T extends Element>(selector: string): T | null => {
    return document.querySelector<T>(selector);
};

const formatarDinheiroProduto = (valor: number): string => new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
}).format(Number.isFinite(valor) ? valor : 0);

const escapeHtml = (valor: unknown): string => {
    const div = document.createElement('div');
    div.textContent = String(valor ?? '');
    return div.innerHTML;
};

const caminhoImagem = (caminho: string | null | undefined): string => {
    if (!caminho) return '';
    return caminho.startsWith('/') ? caminho : `/${caminho}`;
};

let categorias: CategoriaAdmin[] = [];
type ModalProduto = { show(): void; hide(): void; };

let modal: ModalProduto | null = null;

const mostrarAlertaProduto = (
    mensagem: string,
    tipo: 'success' | 'danger' | 'warning' = 'success'
): void => {
    const alerta = $('#produtoAlert') as HTMLDivElement | null;
    if (!alerta) return;

    alerta.textContent = mensagem;
    alerta.className = `alert alert-${tipo}`;

    window.setTimeout(() => {
        alerta.classList.add('d-none');
    }, 5000);
};

const preencherCategorias = (): void => {
    const filtro = $('#filtroCategoria') as HTMLSelectElement | null;
    const select = $('#produtoCategoria') as HTMLSelectElement | null;

    const opcoes = categorias.map((categoria) => (
        `<option value="${categoria.id_categoria}">${escapeHtml(categoria.nome)}</option>`
    )).join('');

    if (filtro) {
        filtro.innerHTML = '<option value="">Todas as categorias</option>' + opcoes;
    }

    if (select) {
        select.innerHTML = '<option value="">Selecione...</option>' + opcoes;
    }
};

const carregarCategorias = async (): Promise<void> => {
    const resultado = await window.drozApi.getJson<CategoriaAdmin[]>(
        `${API_PRODUTOS}?acao=categorias`
    );

    if (!resultado.sucesso || !resultado.dados) {
        throw new Error(resultado.mensagem ?? 'Não foi possível carregar as categorias.');
    }

    categorias = Array.isArray(resultado.dados) ? resultado.dados : [];
    preencherCategorias();
};

const carregarProdutos = async (): Promise<void> => {
    const tbody = $('#produtosTableBody');
    const count = $('#produtoCount');

    if (!tbody || !count) return;

    const busca = ($('#buscaProduto') as HTMLInputElement | null)?.value.trim() ?? '';
    const idCategoria = ($('#filtroCategoria') as HTMLSelectElement | null)?.value ?? '';

    const params = new URLSearchParams({ acao: 'listar' });

    if (busca) params.set('busca', busca);
    if (idCategoria) params.set('id_categoria', idCategoria);

    tbody.innerHTML = '<tr><td colspan="8" class="text-white-50">Carregando produtos...</td></tr>';

    try {
        const resultado = await window.drozApi.getJson<ProdutoAdmin[]>(
            `${API_PRODUTOS}?${params.toString()}`
        );

        if (!resultado.sucesso || !resultado.dados) {
            throw new Error(resultado.mensagem ?? 'Não foi possível carregar os produtos.');
        }

        const produtos = Array.isArray(resultado.dados) ? resultado.dados : [];
        count.textContent = String(produtos.length);

        if (produtos.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="8" class="text-white-50">Nenhum produto encontrado.</td></tr>';
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
                    <td>${formatarDinheiroProduto(Number(produto.preco))}</td>
                    <td>${Number(produto.estoque)}</td>
                    <td>
                        <span class="badge ${Number(produto.ativo) === 1 ? 'text-bg-success' : 'text-bg-secondary'}">
                            ${Number(produto.ativo) === 1 ? 'Ativo' : 'Inativo'}
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
    } catch (erro) {
        tbody.innerHTML =
            '<tr><td colspan="8" class="text-danger">Não foi possível carregar os produtos.</td></tr>';

        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Erro ao carregar produtos.',
            'danger'
        );
    }
};

const limparForm = (): void => {
    const form = $('#produtoForm') as HTMLFormElement | null;
    if (!form) return;

    form.reset();

    ($('#produtoId') as HTMLInputElement).value = '';
    ($('#produtoAtivo') as HTMLInputElement).checked = true;
    ($('#produtoEstoque') as HTMLInputElement).value = '0';

    const titulo = $('#produtoModalLabel');
    if (titulo) titulo.textContent = 'Novo produto';

    const imagensSecao = $('#imagensAtuaisSecao');
    if (imagensSecao) imagensSecao.classList.add('d-none');

    const atuais = $('#imagensAtuais');
    if (atuais) atuais.innerHTML = '';

    const preview = $('#previewNovasImagens');
    if (preview) preview.innerHTML = '';
};

const preencherForm = (produto: ProdutoAdmin): void => {
    ($('#produtoId') as HTMLInputElement).value = String(produto.id_produto);
    ($('#produtoNome') as HTMLInputElement).value = produto.nome;
    ($('#produtoCategoria') as HTMLSelectElement).value = String(produto.id_categoria);

    ($('#produtoPreco') as HTMLInputElement).value =
        Number(produto.preco).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    ($('#produtoEstoque') as HTMLInputElement).value = String(produto.estoque);

    // Corrigido: Number("0") === 0 e Number("1") === 1.
    // Boolean("0") seria true, causando o switch sempre ligado ao editar.
    ($('#produtoAtivo') as HTMLInputElement).checked =
        Number(produto.ativo) === 1;

    ($('#produtoDescricao') as HTMLTextAreaElement).value =
        produto.descricao ?? '';

    const titulo = $('#produtoModalLabel');
    if (titulo) titulo.textContent = `Editar produto #${produto.id_produto}`;

    renderImagensAtuais(produto.imagens ?? []);
};

const renderImagensAtuais = (imagens: ProdutoImagem[]): void => {
    const secao = $('#imagensAtuaisSecao');
    const container = $('#imagensAtuais');

    if (!secao || !container) return;

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

const previewNovasImagens = (): void => {
    const input = $('#produtoImagens') as HTMLInputElement | null;
    const container = $('#previewNovasImagens');

    if (!input || !container) return;

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

const abrirNovo = (): void => {
    limparForm();
    modal?.show();
};

const abrirEditar = async (id: number): Promise<void> => {
    try {
        const resultado = await window.drozApi.getJson<ProdutoAdmin>(
            `${API_PRODUTOS}?acao=buscar&id=${id}`
        );

        if (!resultado.sucesso || !resultado.dados) {
            throw new Error(resultado.mensagem ?? 'Produto não encontrado.');
        }

        preencherForm(resultado.dados);
        modal?.show();
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Não foi possível carregar o produto.',
            'danger'
        );
    }
};

const salvarProduto = async (event: SubmitEvent): Promise<void> => {
    event.preventDefault();

    const form = $('#produtoForm') as HTMLFormElement | null;
    const botao = $('#btnSalvarProduto') as HTMLButtonElement | null;
    const produtoAtivo = $('#produtoAtivo') as HTMLInputElement | null;

    if (!form || !botao || !produtoAtivo) return;

    const formData = new FormData(form);

    // Checkbox desmarcado não entra no FormData automaticamente.
    // Forçamos sempre 1 ou 0 para o PHP receber o estado correto.
    formData.set('ativo', produtoAtivo.checked ? '1' : '0');

    botao.disabled = true;
    botao.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';

    try {
        const resultado = await window.drozApi.postFormData<unknown>(
            API_PRODUTOS,
            formData
        );

        if (!resultado.sucesso) {
            throw new Error(
                resultado.mensagem ?? 'Não foi possível salvar o produto.'
            );
        }

        modal?.hide();
        mostrarAlertaProduto(
            resultado.mensagem ?? 'Produto salvo com sucesso.'
        );

        await carregarProdutos();
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Erro ao salvar produto.',
            'danger'
        );
    } finally {
        botao.disabled = false;
        botao.innerHTML =
            '<i class="bi bi-check2-circle me-1"></i> Salvar produto';
    }
};

const excluirProduto = async (id: number, nome: string): Promise<void> => {
    const confirmou = window.confirm(
        `Deseja realmente excluir o produto "${nome}"?\n\n` +
        'Se ele já possuir histórico de pedidos, o sistema bloqueará a exclusão e você poderá desativá-lo.'
    );

    if (!confirmou) return;

    const formData = new FormData();
    formData.set('acao', 'excluir');
    formData.set('id_produto', String(id));

    try {
        const resultado = await window.drozApi.postFormData<unknown>(
            API_PRODUTOS,
            formData
        );

        if (!resultado.sucesso) {
            throw new Error(
                resultado.mensagem ?? 'Não foi possível excluir o produto.'
            );
        }

        mostrarAlertaProduto(
            resultado.mensagem ?? 'Produto excluído com sucesso.'
        );

        await carregarProdutos();
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Erro ao excluir produto.',
            'danger'
        );
    }
};

const ligarEventos = (): void => {
    $('#btnNovoProduto')?.addEventListener('click', abrirNovo);

    $('#produtoForm')?.addEventListener('submit', (event) => {
        void salvarProduto(event as SubmitEvent);
    });

    $('#produtoImagens')?.addEventListener('change', previewNovasImagens);

    $('#buscaProduto')?.addEventListener('input', () => {
        window.clearTimeout(
            (window as unknown as { produtoSearchTimer?: number })
                .produtoSearchTimer
        );

        (window as unknown as { produtoSearchTimer?: number })
            .produtoSearchTimer = window.setTimeout(() => {
                void carregarProdutos();
            }, 350);
    });

    $('#filtroCategoria')?.addEventListener('change', () => {
        void carregarProdutos();
    });

    $('#produtosTableBody')?.addEventListener('click', (event) => {
        const alvo = event.target as HTMLElement;

        const editar = alvo.closest<HTMLElement>(
            '[data-editar-produto]'
        );

        const excluir = alvo.closest<HTMLElement>(
            '[data-excluir-produto]'
        );

        if (editar) {
            void abrirEditar(Number(editar.dataset.editarProduto));
            return;
        }

        if (excluir) {
            void excluirProduto(
                Number(excluir.dataset.excluirProduto),
                excluir.dataset.nomeProduto ?? 'este produto'
            );
        }
    });
};

document.addEventListener('DOMContentLoaded', async () => {
    if (!window.drozApi) return;

    try {
        const ModalClass = window.bootstrap?.Modal;

        if (ModalClass) {
            const elemento = $('#produtoModal') as HTMLElement | null;

            if (elemento) {
                modal = new ModalClass(elemento);
            }
        }

        ligarEventos();
        await carregarCategorias();
        await carregarProdutos();
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error
                ? erro.message
                : 'Não foi possível iniciar o gerenciamento de produtos.',
            'danger'
        );
    }
});
