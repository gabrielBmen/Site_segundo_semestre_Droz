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
    preco: number | null;
    estoque: number;
    permite_pedido: boolean;
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

interface ClienteVenda {
    id_cliente: number;
    nome: string;
    email: string;
}

interface ProdutoVenda {
    id_produto: number;
    nome: string;
    estoque: number;
    preco: number | null;
    permite_pedido: boolean;
    ativo: boolean;
}

interface DadosVenda {
    clientes: ClienteVenda[];
    produtos: ProdutoVenda[];
}

const API_PRODUTOS = '/api/produtos.php';

const $ = <T extends Element>(selector: string): T | null => {
    return document.querySelector<T>(selector);
};

const definirValor = (selector: string, valor: string): void => {
    const campo = $(selector);
    if (campo instanceof HTMLInputElement || campo instanceof HTMLSelectElement || campo instanceof HTMLTextAreaElement) {
        campo.value = valor;
    }
};

const definirMarcado = (selector: string, marcado: boolean): void => {
    const campo = $(selector);
    if (campo instanceof HTMLInputElement) campo.checked = marcado;
};

const formatarDinheiroProduto = (valor: number | null): string => {
    if (valor === null || !Number.isFinite(valor)) return '—';

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
};

const lerDinheiroProduto = (valor: string): number | null => {
    let texto = valor.replace('R$', '').replace(/\s+/g, '').trim();

    if (texto.includes(',') && texto.includes('.')) {
        texto = texto.replace(/\./g, '').replace(',', '.');
    } else if (texto.includes(',')) {
        texto = texto.replace(',', '.');
    }

    const numero = Number(texto);
    return texto !== '' && Number.isFinite(numero) ? numero : null;
};

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
let clientesVenda: ClienteVenda[] = [];
let produtosVenda: ProdutoVenda[] = [];
type ModalProduto = { show(): void; hide(): void; };

let modalProduto: ModalProduto | null = null;
let modalVenda: ModalProduto | null = null;

const sincronizarCampoPreco = (): void => {
    const campoPreco = $('#produtoPreco');
    const permitePedido = $('#produtoPermitePedido');
    const ajuda = $('#produtoPrecoAjuda');

    if (!(campoPreco instanceof HTMLInputElement)
        || !(permitePedido instanceof HTMLInputElement)) return;

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

const mostrarAlertaProduto = (
    mensagem: string,
    tipo: 'success' | 'danger' | 'warning' = 'success'
): void => {
    const alerta = $('#produtoAlert');
    if (!alerta) return;

    alerta.textContent = mensagem;
    alerta.className = `alert alert-${tipo}`;

    window.setTimeout(() => {
        alerta.classList.add('d-none');
    }, 5000);
};

const preencherCategorias = (): void => {
    const filtro = $('#filtroCategoria');
    const select = $('#produtoCategoria');

    const opcoes = categorias.map((categoria) => (
        `<option value="${categoria.id_categoria}">${escapeHtml(categoria.nome)}</option>`
    )).join('');

    if (filtro instanceof HTMLSelectElement) {
        filtro.innerHTML = '<option value="">Todas as categorias</option>' + opcoes;
    }

    if (select instanceof HTMLSelectElement) {
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

    const buscaCampo = $('#buscaProduto');
    const categoriaCampo = $('#filtroCategoria');
    const canalCampo = $('#filtroCanal');
    const busca = buscaCampo instanceof HTMLInputElement ? buscaCampo.value.trim() : '';
    const idCategoria = categoriaCampo instanceof HTMLSelectElement ? categoriaCampo.value : '';
    const canal = canalCampo instanceof HTMLSelectElement ? canalCampo.value : '';

    const params = new URLSearchParams({ acao: 'listar' });

    if (busca) params.set('busca', busca);
    if (idCategoria) params.set('id_categoria', idCategoria);
    if (canal) params.set('canal', canal);

    tbody.innerHTML = '<tr><td colspan="9" class="text-white-50">Carregando produtos...</td></tr>';

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
                        <button class="btn btn-sm btn-outline-success me-1" type="button" data-vender-produto="${produto.id_produto}" title="${produto.estoque > 0 && produto.ativo ? 'Registrar venda' : 'Produto sem estoque ou inativo'}" ${produto.estoque > 0 && produto.ativo ? '' : 'disabled'}>
                            <i class="bi bi-cash-coin"></i>
                        </button>
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
            '<tr><td colspan="9" class="text-danger">Não foi possível carregar os produtos.</td></tr>';

        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Erro ao carregar produtos.',
            'danger'
        );
    }
};

const limparForm = (): void => {
    const form = $('#produtoForm');
    if (!(form instanceof HTMLFormElement)) return;

    form.reset();

    definirValor('#produtoId', '');
    definirMarcado('#produtoAtivo', true);
    definirMarcado('#produtoPermitePedido', false);
    definirValor('#produtoEstoque', '0');
    sincronizarCampoPreco();

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
    const input = $('#produtoImagens');
    const container = $('#previewNovasImagens');

    if (!(input instanceof HTMLInputElement) || !container) return;

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
    modalProduto?.show();
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
        modalProduto?.show();
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Não foi possível carregar o produto.',
            'danger'
        );
    }
};

const salvarProduto = async (event: Event): Promise<void> => {
    event.preventDefault();

    const form = $('#produtoForm');
    const botao = $('#btnSalvarProduto');
    const produtoAtivo = $('#produtoAtivo');
    const produtoPermitePedido = $('#produtoPermitePedido');

    if (!(form instanceof HTMLFormElement)
        || !(botao instanceof HTMLButtonElement)
        || !(produtoAtivo instanceof HTMLInputElement)
        || !(produtoPermitePedido instanceof HTMLInputElement)) return;

    const formData = new FormData(form);

    // Checkbox desmarcado não entra no FormData automaticamente.
    // Forçamos sempre 1 ou 0 para o PHP receber o estado correto.
    formData.set('ativo', produtoAtivo.checked ? '1' : '0');
    formData.set('permite_pedido', produtoPermitePedido.checked ? '1' : '0');

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

        modalProduto?.hide();
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
    const csrf = $('#produtoCsrfToken');
    if (!(csrf instanceof HTMLInputElement) || csrf.value === '') {
        mostrarAlertaProduto('Sua sessão expirou. Atualize a página e tente novamente.', 'danger');
        return;
    }
    formData.set('csrf_token', csrf.value);

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

const preencherOpcoesVenda = (): void => {
    const clienteCampo = $('#vendaCliente');
    const buscaClienteCampo = $('#vendaClienteBusca');
    const produtoCampo = $('#vendaProduto');

    if (clienteCampo instanceof HTMLSelectElement) {
        const clienteSelecionado = clienteCampo.value;
        const termo = buscaClienteCampo instanceof HTMLInputElement
            ? buscaClienteCampo.value.trim().toLocaleLowerCase('pt-BR')
            : '';
        const clientesFiltrados = termo === ''
            ? clientesVenda
            : clientesVenda.filter((cliente) => (
                `${cliente.nome} ${cliente.email}`.toLocaleLowerCase('pt-BR').includes(termo)
            ));
        const rotuloInicial = clientesFiltrados.length > 0
            ? 'Selecione o cliente...'
            : 'Nenhum cliente encontrado';

        clienteCampo.innerHTML = `<option value="">${rotuloInicial}</option>` +
            clientesFiltrados.map((cliente) => (
                `<option value="${cliente.id_cliente}">${escapeHtml(cliente.nome)} — ${escapeHtml(cliente.email)}</option>`
            )).join('');

        if (clientesFiltrados.some((cliente) => String(cliente.id_cliente) === clienteSelecionado)) {
            clienteCampo.value = clienteSelecionado;
        }
    }

    if (produtoCampo instanceof HTMLSelectElement) {
        produtoCampo.innerHTML = '<option value="">Selecione o produto...</option>' +
            produtosVenda.map((produto) => {
                const canal = produto.permite_pedido ? 'pedido online' : 'sob orçamento';
                const indisponivel = !produto.ativo || produto.estoque <= 0;
                const estado = !produto.ativo ? 'inativo' : `${produto.estoque} em estoque`;

                return `<option value="${produto.id_produto}" ${indisponivel ? 'disabled' : ''}>${escapeHtml(produto.nome)} — ${canal} — ${estado}</option>`;
            }).join('');
    }
};

const carregarDadosVenda = async (): Promise<void> => {
    const resultado = await window.drozApi.getJson<DadosVenda>(
        `${API_PRODUTOS}?acao=dados_venda`
    );

    if (!resultado.sucesso || !resultado.dados) {
        throw new Error(resultado.mensagem ?? 'Não foi possível preparar o registro da venda.');
    }

    clientesVenda = Array.isArray(resultado.dados.clientes) ? resultado.dados.clientes : [];
    produtosVenda = Array.isArray(resultado.dados.produtos) ? resultado.dados.produtos : [];
    preencherOpcoesVenda();
};

const atualizarTotalVenda = (): void => {
    const quantidadeCampo = $('#vendaQuantidade');
    const precoCampo = $('#vendaPreco');
    const totalElemento = $('#vendaTotal');

    if (!(quantidadeCampo instanceof HTMLInputElement)
        || !(precoCampo instanceof HTMLInputElement)
        || !totalElemento) return;

    const quantidade = Number(quantidadeCampo.value);
    const preco = lerDinheiroProduto(precoCampo.value);
    const total = Number.isInteger(quantidade) && quantidade > 0 && preco !== null && preco > 0
        ? quantidade * preco
        : 0;

    totalElemento.textContent = formatarDinheiroProduto(total);
};

const sincronizarProdutoVenda = (preencherPreco = true): void => {
    const produtoCampo = $('#vendaProduto');
    const quantidadeCampo = $('#vendaQuantidade');
    const precoCampo = $('#vendaPreco');
    const estoqueAjuda = $('#vendaEstoqueAjuda');
    const botao = $('#btnSalvarVenda');

    if (!(produtoCampo instanceof HTMLSelectElement)
        || !(quantidadeCampo instanceof HTMLInputElement)
        || !(precoCampo instanceof HTMLInputElement)) return;

    const produto = produtosVenda.find(
        (item) => item.id_produto === Number(produtoCampo.value)
    );

    if (!produto) {
        quantidadeCampo.removeAttribute('max');
        if (estoqueAjuda) estoqueAjuda.textContent = 'Selecione um produto para consultar o estoque.';
        if (preencherPreco) precoCampo.value = '';
        if (botao instanceof HTMLButtonElement) botao.disabled = true;
        atualizarTotalVenda();
        return;
    }

    quantidadeCampo.max = String(produto.estoque);
    if (Number(quantidadeCampo.value) > produto.estoque || Number(quantidadeCampo.value) < 1) {
        quantidadeCampo.value = produto.estoque > 0 ? '1' : '0';
    }

    if (preencherPreco) {
        precoCampo.value = produto.permite_pedido && produto.preco !== null
            ? produto.preco.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '';
    }

    if (estoqueAjuda) {
        estoqueAjuda.textContent = produto.permite_pedido
            ? `Estoque disponível: ${produto.estoque}. O preço atual foi sugerido e pode ser ajustado para esta venda.`
            : `Estoque disponível: ${produto.estoque}. Informe o valor fechado no orçamento.`;
    }

    if (botao instanceof HTMLButtonElement) {
        botao.disabled = !produto.ativo || produto.estoque <= 0;
    }

    atualizarTotalVenda();
};

const abrirVenda = async (idProduto?: number): Promise<void> => {
    const form = $('#vendaForm');
    const produtoCampo = $('#vendaProduto');

    if (!(form instanceof HTMLFormElement) || !(produtoCampo instanceof HTMLSelectElement)) return;

    try {
        form.reset();
        await carregarDadosVenda();

        if (clientesVenda.length === 0) {
            mostrarAlertaProduto('Cadastre e ative pelo menos um cliente antes de registrar uma venda.', 'warning');
            return;
        }

        const possuiProdutoDisponivel = produtosVenda.some(
            (produto) => produto.ativo && produto.estoque > 0
        );
        if (!possuiProdutoDisponivel) {
            mostrarAlertaProduto('Não há produtos ativos com estoque disponível para venda.', 'warning');
            return;
        }

        if (idProduto !== undefined) {
            produtoCampo.value = String(idProduto);
        }
        sincronizarProdutoVenda(true);
        modalVenda?.show();
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Não foi possível abrir o registro de venda.',
            'danger'
        );
    }
};

const registrarVenda = async (event: Event): Promise<void> => {
    event.preventDefault();

    const form = $('#vendaForm');
    const botao = $('#btnSalvarVenda');
    if (!(form instanceof HTMLFormElement) || !(botao instanceof HTMLButtonElement)) return;

    botao.disabled = true;
    botao.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando...';

    try {
        const resultado = await window.drozApi.postFormData<unknown>(
            API_PRODUTOS,
            new FormData(form)
        );

        if (!resultado.sucesso) {
            throw new Error(resultado.mensagem ?? 'Não foi possível registrar a venda.');
        }

        modalVenda?.hide();
        mostrarAlertaProduto(resultado.mensagem ?? 'Venda registrada com sucesso.');
        await Promise.all([carregarProdutos(), carregarDadosVenda()]);
    } catch (erro) {
        mostrarAlertaProduto(
            erro instanceof Error ? erro.message : 'Erro ao registrar a venda.',
            'danger'
        );
    } finally {
        botao.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirmar venda';
        sincronizarProdutoVenda(false);
    }
};

const ligarEventos = (): void => {
    $('#btnNovoProduto')?.addEventListener('click', abrirNovo);
    $('#btnRegistrarVenda')?.addEventListener('click', () => {
        void abrirVenda();
    });

    $('#produtoForm')?.addEventListener('submit', (event) => {
        void salvarProduto(event);
    });

    $('#vendaForm')?.addEventListener('submit', (event) => {
        void registrarVenda(event);
    });

    $('#vendaProduto')?.addEventListener('change', () => sincronizarProdutoVenda(true));
    $('#vendaQuantidade')?.addEventListener('input', atualizarTotalVenda);
    $('#vendaPreco')?.addEventListener('input', atualizarTotalVenda);

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

    $('#filtroCanal')?.addEventListener('change', () => {
        void carregarProdutos();
    });

    $('#vendaClienteBusca')?.addEventListener('input', preencherOpcoesVenda);

    $('#produtosTableBody')?.addEventListener('click', (event) => {
        const alvo = event.target;
        if (!(alvo instanceof Element)) return;

        const editar = alvo.closest<HTMLElement>(
            '[data-editar-produto]'
        );

        const vender = alvo.closest<HTMLElement>(
            '[data-vender-produto]'
        );

        const excluir = alvo.closest<HTMLElement>(
            '[data-excluir-produto]'
        );

        if (vender) {
            void abrirVenda(Number(vender.dataset.venderProduto));
            return;
        }

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
            const elemento = $('#produtoModal');
            const elementoVenda = $('#vendaModal');

            if (elemento instanceof HTMLElement) {
                modalProduto = new ModalClass(elemento);
            }

            if (elementoVenda instanceof HTMLElement) {
                modalVenda = new ModalClass(elementoVenda);
            }
        }

        ligarEventos();
        await Promise.all([carregarCategorias(), carregarDadosVenda()]);
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
