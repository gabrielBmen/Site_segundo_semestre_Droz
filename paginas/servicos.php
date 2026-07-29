<?php
$tituloPagina = 'Serviços';
$descricaoPagina = 'Serviços de automação industrial, integração e treinamento da DROZ Robótica.';
$paginaAtiva = 'servicos';
require_once __DIR__ . '/includes/funcoes.php';
include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="assets/css/style.css?v=2">
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">Serviços</h1>
            <p class="section-subtitle mx-auto">Página institucional para demonstrar o escopo comercial e técnico da empresa.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="icon-circle mb-3"><i class="bi bi-cpu"></i></div>
                    <h5 class="fw-bold">Integração de robôs</h5>
                    <p class="text-white-50 mb-0">Integração de robôs industriais, fontes e periféricos para aplicações de solda e automação.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="icon-circle mb-3"><i class="bi bi-tools"></i></div>
                    <h5 class="fw-bold">Projetos sob medida</h5>
                    <p class="text-white-50 mb-0">Desenvolvimento de células customizadas conforme o processo, espaço disponível e meta de produção.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="icon-circle mb-3"><i class="bi bi-person-workspace"></i></div>
                    <h5 class="fw-bold">Treinamento e suporte</h5>
                    <p class="text-white-50 mb-0">Capacitação para operadores e programadores com foco em autonomia e continuidade operacional.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
