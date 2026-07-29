<?php
$tituloPagina = 'Sobre';
$descricaoPagina = 'Conheça a história, os diferenciais e a atuação da DROZ Robótica.';
$paginaAtiva = 'sobre';
require_once __DIR__ . '/includes/funcoes.php';
include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="assets/css/style.css?v=2">
<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h1 class="section-title mb-3">Sobre a DROZ Robótica</h1>
                <p class="hero-text">
                    A DROZ Robótica é uma indústria brasileira especializada na fabricação de células robotizadas de solda e soluções em automação industrial. A empresa é sediada em Campo Mourão, no Paraná, e se apresenta publicamente como fabricante de células robotizadas de solda com engenharia própria, foco em produtividade e repetibilidade.
                </p>
                <p class="text-white-50">
                    A empresa destaca atuação em estruturas industriais robustas, segurança operacional, treinamento técnico e soluções para a indústria metalmecânica.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="glass-card rounded-4 p-4 p-lg-5">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="stat-box">
                                <p class="stat-number">2022</p>
                                <p class="stat-label">Ano de fundação informado no perfil público</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat-box">
                                <p class="stat-number">PR</p>
                                <p class="stat-label">Base em Campo Mourão</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-card rounded-4 p-4">
                                <h5 class="fw-bold">Missão do site</h5>
                                <p class="text-white-50 mb-0">Apresentar a empresa, mostrar portfólio e gerar oportunidades comerciais com uma navegação clara e responsiva.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-4">
                <div class="info-card rounded-4 p-4 h-100">
                    <h5 class="fw-bold">Compromisso</h5>
                    <p class="text-white-50 mb-0">Tecnologia nacional, suporte técnico e projetos pensados para operação real em fábrica.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="info-card rounded-4 p-4 h-100">
                    <h5 class="fw-bold">Foco</h5>
                    <p class="text-white-50 mb-0">Soldagem robotizada, automação industrial, integração e treinamento para autonomia do cliente.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="info-card rounded-4 p-4 h-100">
                    <h5 class="fw-bold">Resultado</h5>
                    <p class="text-white-50 mb-0">Mais produtividade, menos retrabalho e processos mais estáveis e previsíveis.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
