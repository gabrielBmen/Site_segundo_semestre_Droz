<?php
require_once __DIR__ . '/../../includes/csrf.php';
$adminPagina = 'Produtos';
include __DIR__ . '/includes/header.php';
?>

<div id="produtoAlert" class="alert d-none" role="alert"></div>

<div class="dashboard-card mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label for="buscaProduto" class="form-label">Pesquisar</label>
            <div class="input-group">
                <span class="input-group-text bg-dark text-white border-secondary"><i class="bi bi-search"></i></span>
                <input type="search" id="buscaProduto" class="form-control bg-dark text-white border-secondary" placeholder="Nome, descrição ou slug...">
            </div>
        </div>
        <div class="col-lg-2">
            <label for="filtroCategoria" class="form-label">Categoria</label>
            <select id="filtroCategoria" class="form-select bg-dark text-white border-secondary">
                <option value="">Todas as categorias</option>
            </select>
        </div>
        <div class="col-lg-3">
            <label for="filtroCanal" class="form-label">Canal de venda</label>
            <select id="filtroCanal" class="form-select bg-dark text-white border-secondary">
                <option value="">Todos os canais</option>
                <option value="online">Pedido online</option>
                <option value="orcamento">Sob orçamento</option>
            </select>
        </div>
        <div class="col-lg-3 d-grid gap-2">
            <button id="btnRegistrarVenda" type="button" class="btn btn-outline-success">
                <i class="bi bi-cash-coin me-1"></i> Registrar venda
            </button>
            <button id="btnNovoProduto" type="button" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Novo produto
            </button>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="text-uppercase small text-white-50">Catálogo</div>
            <h2 class="h5 mb-0">Produtos cadastrados</h2>
        </div>
        <span id="produtoCount" class="badge text-bg-secondary">0</span>
    </div>

    <div class="table-responsive">
        <table class="table table-dark align-middle mb-0">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Canal</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="produtosTableBody">
                <tr><td colspan="9" class="text-white-50">Carregando produtos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="vendaModal" tabindex="-1" aria-labelledby="vendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form id="vendaForm">
                <div class="modal-header border-secondary">
                    <div>
                        <h2 class="modal-title h5" id="vendaModalLabel">Registrar venda</h2>
                        <div class="small text-white-50">Informe o valor realmente negociado. O preço público do catálogo não será alterado.</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="acao" value="registrar_venda">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="vendaClienteBusca" class="form-label">Pesquisar cliente</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-dark text-white border-secondary"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control bg-dark text-white border-secondary" id="vendaClienteBusca" placeholder="Digite o nome ou e-mail..." autocomplete="off">
                            </div>

                            <label for="vendaCliente" class="form-label">Cliente *</label>
                            <select class="form-select bg-dark text-white border-secondary" id="vendaCliente" name="id_cliente" required>
                                <option value="">Carregando clientes...</option>
                            </select>
                            <div class="form-text text-white-50">A venda ficará vinculada ao histórico deste cliente.</div>
                        </div>

                        <div class="col-12">
                            <label for="vendaProduto" class="form-label">Produto vendido *</label>
                            <select class="form-select bg-dark text-white border-secondary" id="vendaProduto" name="id_produto" required>
                                <option value="">Carregando produtos...</option>
                            </select>
                            <div id="vendaEstoqueAjuda" class="form-text text-white-50">Selecione um produto para consultar o estoque.</div>
                        </div>

                        <div class="col-md-5">
                            <label for="vendaQuantidade" class="form-label">Quantidade *</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary" id="vendaQuantidade" name="quantidade" min="1" step="1" value="1" required>
                        </div>

                        <div class="col-md-7">
                            <label for="vendaPreco" class="form-label">Preço unitário negociado (R$) *</label>
                            <input type="text" inputmode="decimal" class="form-control bg-dark text-white border-secondary" id="vendaPreco" name="preco_unitario" placeholder="0,00" required>
                            <div class="form-text text-white-50">Esse valor será salvo apenas nesta venda.</div>
                        </div>

                        <div class="col-12">
                            <div class="admin-info-box d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <div class="small text-white-50">Total da venda</div>
                                    <strong id="vendaTotal" class="fs-4">R$ 0,00</strong>
                                </div>
                                <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Será registrada como concluída</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-secondary">
                    <a href="/admin/pedidos.php" class="btn btn-link text-white-50 me-auto">Ver histórico de pedidos</a>
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnSalvarVenda" type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Confirmar venda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="produtoModal" tabindex="-1" aria-labelledby="produtoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary">
            <form id="produtoForm" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <div>
                        <h2 class="modal-title h5" id="produtoModalLabel">Novo produto</h2>
                        <div class="small text-white-50">Cadastre os dados comerciais e as imagens do produto.</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_produto" id="produtoId">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="csrf_token" id="produtoCsrfToken" value="<?= e(csrfToken()) ?>">

                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label for="produtoNome" class="form-label">Nome *</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="produtoNome" name="nome" maxlength="120" required>
                        </div>

                        <div class="col-lg-4">
                            <label for="produtoCategoria" class="form-label">Categoria *</label>
                            <select class="form-select bg-dark text-white border-secondary" id="produtoCategoria" name="id_categoria" required>
                                <option value="">Selecione...</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="produtoPreco" class="form-label">Preço (R$)</label>
                            <input type="text" inputmode="decimal" class="form-control bg-dark text-white border-secondary" id="produtoPreco" name="preco" placeholder="0,00">
                            <div id="produtoPrecoAjuda" class="form-text text-white-50">Disponível apenas para produtos com pedido online.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="produtoEstoque" class="form-label">Estoque *</label>
                            <input type="number" min="0" step="1" class="form-control bg-dark text-white border-secondary" id="produtoEstoque" name="estoque" value="0" required>
                        </div>

                        <div class="col-md-4 d-flex flex-column justify-content-end gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="produtoAtivo" name="ativo" value="1" checked>
                                <label class="form-check-label" for="produtoAtivo">Produto ativo no catálogo</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="produtoPermitePedido" name="permite_pedido" value="1">
                                <label class="form-check-label" for="produtoPermitePedido">Disponível para pedido online</label>
                                <div class="form-text text-white-50">Produtos sob orçamento não possuem preço cadastrado.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="produtoDescricao" class="form-label">Descrição</label>
                            <textarea class="form-control bg-dark text-white border-secondary" id="produtoDescricao" name="descricao" rows="5" placeholder="Descreva a solução, aplicação, diferenciais, especificações etc."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="produtoImagens" class="form-label">Adicionar fotos</label>
                            <input class="form-control bg-dark text-white border-secondary" type="file" id="produtoImagens" name="imagens[]" accept="image/jpeg,image/png,image/webp" multiple>
                            <div class="form-text text-white-50">JPG, PNG ou WEBP. Até 5 MB por imagem.</div>
                            <div id="previewNovasImagens" class="row g-2 mt-1"></div>
                        </div>
                    </div>

                    <div id="imagensAtuaisSecao" class="mt-4 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-semibold">Fotos atuais</div>
                                <div class="small text-white-50">Escolha a imagem principal ou marque fotos para remover.</div>
                            </div>
                        </div>
                        <div id="imagensAtuais" class="row g-3"></div>
                    </div>
                </div>

                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnSalvarProduto" type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Salvar produto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/api.js?v=2"></script>
<script src="/assets/js/admin-produtos.js?v=6"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
