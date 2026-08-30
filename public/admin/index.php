<?php
$adminPagina = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div id="dashboardAlert" class="alert d-none" role="alert"></div>

<form id="dashboardFilters" class="dashboard-card mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-4 col-lg-3">
            <label for="periodoDashboard" class="form-label">Período</label>
            <select id="periodoDashboard" class="form-select bg-dark text-white border-secondary">
                <option value="mes" selected>Últimos 30 dias</option>
                <option value="semana">Últimos 7 dias</option>
                <option value="personalizado">Datas específicas</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-3 dashboard-custom-date d-none">
            <label for="dataInicioDashboard" class="form-label">Data inicial</label>
            <input id="dataInicioDashboard" type="date" class="form-control bg-dark text-white border-secondary">
        </div>
        <div class="col-md-4 col-lg-3 dashboard-custom-date d-none">
            <label for="dataFimDashboard" class="form-label">Data final</label>
            <input id="dataFimDashboard" type="date" class="form-control bg-dark text-white border-secondary">
        </div>
        <div class="col-md-6 col-lg-3">
            <label for="buscaDashboard" class="form-label">Buscar</label>
            <input id="buscaDashboard" type="search" maxlength="120" class="form-control bg-dark text-white border-secondary" placeholder="Pedido, cliente ou produto">
        </div>
        <div class="col-md-3 col-lg-2">
            <label for="statusDashboard" class="form-label">Status</label>
            <select id="statusDashboard" class="form-select bg-dark text-white border-secondary">
                <option value="">Todos</option>
                <option value="pendente">Pendente</option>
                <option value="aprovado">Aprovado</option>
                <option value="concluido">Concluído</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label for="porPaginaDashboard" class="form-label">Itens por página</label>
            <select id="porPaginaDashboard" class="form-select bg-dark text-white border-secondary">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-3 d-grid">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Atualizar análise</button>
        </div>
    </div>
    <div id="dashboardPeriodLabel" class="form-text text-white-50 mt-3">Exibindo vendas dos últimos 30 dias.</div>
</form>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="text-white-50 small">Faturamento no período</div>
            <div id="metricFaturamento" class="metric-value">R$ 0,00</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-receipt"></i></div>
            <div class="text-white-50 small">Pedidos no período</div>
            <div id="metricPedidos" class="metric-value">0</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-box-seam"></i></div>
            <div class="text-white-50 small">Itens vendidos no período</div>
            <div id="metricItens" class="metric-value">0</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="text-white-50 small">Faturamento médio mensal</div>
            <div id="metricTicket" class="metric-value">R$ 0,00</div>
        </div>
    </div>
</div>

<div id="emptyState" class="dashboard-card empty-state d-none mb-4">
    <i class="bi bi-database-x display-6"></i>
    <h2 class="h5 mt-3">Nenhum dado registrado</h2>
    <p class="text-white-50 mb-0">Cadastre pedidos para visualizar as métricas e os rankings da dashboard.</p>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="text-uppercase small text-white-50">Análise</div>
                    <h2 class="h5 mb-0">Status dos pedidos</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Pedidos</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody id="statusTableBody">
                        <tr><td colspan="3" class="text-white-50">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="dashboard-card h-100">
            <div class="text-uppercase small text-white-50">Ranking</div>
            <h2 class="h5 mb-3">Produtos mais vendidos</h2>
            <div id="topProductsList" class="d-flex flex-column gap-2">
                <div class="text-white-50">Carregando...</div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="text-uppercase small text-white-50">Dados utilizados</div>
            <h2 class="h5 mb-0">Itens consolidados</h2>
        </div>
        <span id="rawDataCount" class="badge text-bg-secondary">0</span>
    </div>
    <div class="table-responsive">
        <table class="table table-dark align-middle mb-0">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Produto</th>
                    <th>Qtd.</th>
                    <th>Unitário</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="rawDataTableBody">
                <tr><td colspan="6" class="text-white-50">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
    <nav class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3" aria-label="Paginação dos dados da dashboard">
        <small id="dashboardPaginationLabel" class="text-white-50">Página 1 de 1</small>
        <div class="btn-group" role="group" aria-label="Navegação entre páginas">
            <button id="dashboardPreviousPage" type="button" class="btn btn-outline-light btn-sm">Anterior</button>
            <button id="dashboardNextPage" type="button" class="btn btn-outline-light btn-sm">Próxima</button>
        </div>
    </nav>
</div>

<script src="/assets/js/api.js?v=1"></script>
<script src="/assets/js/dashboard.js?v=3"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
