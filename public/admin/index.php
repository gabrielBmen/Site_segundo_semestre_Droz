<?php
$adminPagina = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div id="dashboardAlert" class="alert d-none" role="alert"></div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="text-white-50 small">Faturamento total</div>
            <div id="metricFaturamento" class="metric-value">R$ 0,00</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-receipt"></i></div>
            <div class="text-white-50 small">Pedidos</div>
            <div id="metricPedidos" class="metric-value">0</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-box-seam"></i></div>
            <div class="text-white-50 small">Itens vendidos</div>
            <div id="metricItens" class="metric-value">0</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-card metric-card">
            <div class="metric-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="text-white-50 small">Ticket médio</div>
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
</div>

<script src="/assets/js/api.js?v=1"></script>
<script src="/assets/js/dashboard.js?v=1"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
