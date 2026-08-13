<?php

$tituloPagina = 'Home';
$descricaoPagina = 'Site institucional e de vendas da DROZ Robótica.';
$paginaAtiva = 'home';

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';


// =========================================================
// BUSCAR PRODUTOS DO BANCO
// =========================================================

$sql = "
    SELECT
        p.id_produto,
        p.id_categoria,
        p.nome,
        p.slug,
        p.descricao,
        p.preco,
        p.estoque,
        p.ativo,
        c.nome AS categoria,
        i.caminho AS imagem

    FROM produtos p

    INNER JOIN categorias c
        ON c.id_categoria = p.id_categoria

    LEFT JOIN imagens_produto i
        ON i.id_produto = p.id_produto
        AND i.principal = TRUE

    WHERE p.ativo = TRUE

    ORDER BY p.id_produto ASC
";

$stmt = $pdo->query($sql);

$catalogoProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// VALIDAR PRODUTOS
// =========================================================

if (!validarListaProdutos($catalogoProdutos)) {
    $catalogoProdutos = [];
}


// =========================================================
// PRODUTOS EM DESTAQUE
// =========================================================

$destaques = array_slice($catalogoProdutos, 0, 3);


// =========================================================
// PRODUTOS ACIMA DE R$ 90.000
// =========================================================

$produtosCaros = filtrarProdutosPorPreco(
    $catalogoProdutos,
    90000
);


// =========================================================
// BUSCAR PRODUTO CSR1
// =========================================================

$produtoCsr1 = buscarProdutoPorSlug(
    $catalogoProdutos,
    'celula-robotizada-csr1'
);


// =========================================================
// CALCULAR DESCONTO
// =========================================================

$precoBase = $produtoCsr1
    ? $produtoCsr1['preco']
    : 0;

$descontoExemplo = aplicarDesconto(
    $precoBase,
    8
);


// =========================================================
// CARROSSEL
// =========================================================
//
// Agora o carrossel também utiliza as imagens
// cadastradas no banco de dados.
//
// Buscamos todas as imagens do produto CSR1.
//

$slidesCarrossel = [];

if ($produtoCsr1) {

    $sqlImagens = "
        SELECT
            caminho,
            principal

        FROM imagens_produto

        WHERE id_produto = :id_produto

        ORDER BY principal DESC, id_imagem ASC
    ";

    $stmtImagens = $pdo->prepare($sqlImagens);

    $stmtImagens->execute([
        ':id_produto' => $produtoCsr1['id_produto']
    ]);

    $imagens = $stmtImagens->fetchAll(PDO::FETCH_ASSOC);


    foreach ($imagens as $imagem) {

        $slidesCarrossel[] = [
            'imagem' => $imagem['caminho'],
            'nome' => $produtoCsr1['nome']
        ];

    }
}


include __DIR__ . '/../includes/header.php';

?>

<link rel="stylesheet" href="assets/css/style.css?v=2">


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">

    <div class="container position-relative">

        <div class="row g-4 align-items-stretch">


            <!-- TEXTO -->
            <div class="col-lg-6">

                <span class="badge-soft mb-3">
                    <i class="bi bi-gear-fill"></i>
                    Robótica industrial com engenharia própria
                </span>

                <h1 class="hero-title mb-4">
                    Automação que aumenta
                    <span>produtividade</span>,
                    <span>segurança</span> e
                    <span>padronização</span>.
                </h1>

                <p class="hero-text mb-4">
                    A DROZ Robótica desenvolve e fabrica células
                    robotizadas de solda, soluções de automação
                    industrial e projetos sob medida para indústrias
                    que precisam de mais desempenho no chão de fábrica.
                </p>


                <div class="d-flex flex-wrap gap-3">

                    <a href="produtos.php"
                       class="btn btn-primary btn-lg px-4">

                        Ver catálogo

                    </a>


                    <a href="contato.php"
                       class="btn btn-outline-light btn-lg px-4">

                        Solicitar orçamento

                    </a>

                </div>


                <div class="row g-3 mt-4">

                    <div class="col-6 col-md-4">

                        <div class="stat-box">

                            <p class="stat-number">
                                2022
                            </p>

                            <p class="stat-label">
                                Fundação da empresa
                            </p>

                        </div>

                    </div>


                    <div class="col-6 col-md-4">

                        <div class="stat-box">

                            <p class="stat-number">
                                NR-12
                            </p>

                            <p class="stat-label">
                                Foco em segurança
                            </p>

                        </div>

                    </div>


                    <div class="col-12 col-md-4">

                        <div class="stat-box">

                            <p class="stat-number">
                                Brasil
                            </p>

                            <p class="stat-label">
                                Engenharia nacional
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- CARROSSEL -->
            <div class="col-lg-6">

                <div class="hero-card">

                    <div id="carrosselHero"
                         class="carousel slide"
                         data-bs-ride="carousel">


                        <div class="carousel-inner rounded-4 overflow-hidden">

                            <?php foreach ($slidesCarrossel as $key => $slide): ?>

                                <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">

                                    <img
                                        src="<?= e($slide['imagem']) ?>"
                                        class="d-block w-100 img-carrossel"
                                        alt="<?= e($slide['nome']) ?>"
                                    >

                                </div>

                            <?php endforeach; ?>


                            <?php if (empty($slidesCarrossel)): ?>

                                <div class="carousel-item active">

                                    <div class="p-5 text-center">

                                        <p class="mb-0">
                                            Nenhuma imagem cadastrada.
                                        </p>

                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>


                        <button
                            class="carousel-control-prev"
                            type="button"
                            data-bs-target="#carrosselHero"
                            data-bs-slide="prev">

                            <span class="carousel-control-prev-icon"></span>

                        </button>


                        <button
                            class="carousel-control-next"
                            type="button"
                            data-bs-target="#carrosselHero"
                            data-bs-slide="next">

                            <span class="carousel-control-next-icon"></span>

                        </button>

                    </div>


                    <div class="row g-3 mt-3">


                        <div class="col-md-6">

                            <div class="glass-card p-3 rounded-4 h-100">

                                <div class="d-flex align-items-center gap-3">

                                    <div class="icon-circle">

                                        <i class="bi bi-shield-check"></i>

                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            Segurança industrial
                                        </div>

                                        <div class="small-muted">
                                            Projetos alinhados à operação real.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="glass-card p-3 rounded-4 h-100">

                                <div class="d-flex align-items-center gap-3">

                                    <div class="icon-circle">

                                        <i class="bi bi-lightning-charge"></i>

                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            Alta eficiência
                                        </div>

                                        <div class="small-muted">
                                            Soluções pensadas para ciclos contínuos.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     O QUE A DROZ ENTREGA
========================================================= -->

<section class="py-5">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="section-title">
                O que a DROZ entrega
            </h2>

            <p class="section-subtitle mx-auto">
                Conteúdo institucional para apresentar o negócio
                e também abrir caminho para venda consultiva de
                soluções industriais.
            </p>

        </div>


        <div class="row g-4">


            <div class="col-md-4">

                <div class="info-card rounded-4 p-4 h-100">

                    <div class="icon-circle mb-3">

                        <i class="bi bi-robot"></i>

                    </div>

                    <h5 class="fw-bold">
                        Células robotizadas
                    </h5>

                    <p class="text-white-50 mb-0">
                        Produção de células padrão para soldagem
                        robotizada com estrutura robusta e foco
                        em repetibilidade.
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="info-card rounded-4 p-4 h-100">

                    <div class="icon-circle mb-3">

                        <i class="bi bi-diagram-3"></i>

                    </div>

                    <h5 class="fw-bold">
                        Engenharia própria
                    </h5>

                    <p class="text-white-50 mb-0">
                        Desenvolvimento de projetos com adaptação
                        à necessidade de cada cliente.
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="info-card rounded-4 p-4 h-100">

                    <div class="icon-circle mb-3">

                        <i class="bi bi-mortarboard"></i>

                    </div>

                    <h5 class="fw-bold">
                        Treinamento técnico
                    </h5>

                    <p class="text-white-50 mb-0">
                        Capacitação de operadores e programadores
                        para acelerar adoção e autonomia.
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- =========================================================
     PRODUTOS EM DESTAQUE
========================================================= -->

<section class="py-5">

    <div class="container">

        <div class="row align-items-end mb-4">

            <div class="col-lg-8">

                <h2 class="section-title">
                    Produtos em destaque
                </h2>

                <p class="section-subtitle">
                    Produtos carregados diretamente do banco de dados.
                </p>

            </div>


            <div class="col-lg-4 text-lg-end">

                <a
                    href="produtos.php"
                    class="btn btn-outline-light">

                    Abrir catálogo completo

                </a>

            </div>

        </div>


        <div class="row g-4">


            <?php foreach ($destaques as $produto): ?>

                <div class="col-lg-4 col-md-6">

                    <article class="product-card">


                        <?php if (!empty($produto['imagem'])): ?>

                            <img
                                src="<?= e($produto['imagem']) ?>"
                                class="d-block w-100 img-carrossel"
                                alt="<?= e($produto['nome']) ?>"
                            >

                        <?php endif; ?>


                        <div class="product-body">

                            <span class="badge badge-category mb-2">

                                <?= e($produto['categoria']) ?>

                            </span>


                            <h5 class="fw-bold">

                                <?= e($produto['nome']) ?>

                            </h5>


                            <p class="small-muted">

                                <?= e($produto['descricao']) ?>

                            </p>


                            <div class="d-flex justify-content-between align-items-center">

                                <div class="price">

                                    <?= moeda($produto['preco']) ?>

                                </div>


                                <a
                                    href="produtos.php#<?= e($produto['slug']) ?>"
                                    class="btn btn-primary btn-sm">

                                    Detalhes

                                </a>

                            </div>

                        </div>

                    </article>

                </div>

            <?php endforeach; ?>


            <?php if (empty($destaques)): ?>

                <div class="col-12">

                    <div class="glass-card rounded-4 p-5 text-center">

                        <h4 class="fw-bold">
                            Nenhum produto cadastrado
                        </h4>

                        <p class="text-white-50 mb-0">
                            Cadastre produtos no banco de dados.
                        </p>

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </div>

</section>



<!-- =========================================================
     DIFERENCIAIS
========================================================= -->

<section class="py-5">

    <div class="container">

        <div class="row g-4 diferenciais-row">


            <div class="col-lg-8">

                <div class="glass-card rounded-4 p-4 p-lg-5">

                    <h2 class="section-title mb-3">
                        Diferenciais da empresa
                    </h2>


                    <div class="timeline">


                        <div class="step">

                            <h6 class="fw-bold mb-1">
                                Produção nacional
                            </h6>

                            <p class="text-white-50 mb-0">
                                A empresa trabalha com engenharia
                                própria e foco em tecnologia aplicada
                                à manufatura.
                            </p>

                        </div>


                        <div class="step">

                            <h6 class="fw-bold mb-1">
                                Processo estável
                            </h6>

                            <p class="text-white-50 mb-0">
                                As células são planejadas para operação
                                contínua e repetibilidade industrial.
                            </p>

                        </div>


                        <div class="step mb-0">

                            <h6 class="fw-bold mb-1">
                                Retorno para o cliente
                            </h6>

                            <p class="text-white-50 mb-0">
                                A proposta é reduzir retrabalho,
                                aumentar segurança e melhorar
                                produtividade no chão de fábrica.
                            </p>

                        </div>


                    </div>

                </div>

            </div>



            <div class="col-lg-4">

                <div class="row g-3">


                    <div class="col-sm-6">

                        <div class="stat-box">

                            <p class="stat-number">
                                <?= count($catalogoProdutos) ?>
                            </p>

                            <p class="stat-label">
                                itens no catálogo
                            </p>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="stat-box">

                            <p class="stat-number">
                                <?= count($produtosCaros) ?>
                            </p>

                            <p class="stat-label">
                                soluções acima de R$ 90 mil
                            </p>

                        </div>

                    </div>


                    <div class="col-12">

                        <div class="glass-card principal-card rounded-4 p-4 h-100">

                            <p class="text-uppercase text-white-50 small mb-2">
                                Lógica de negócio aplicada
                            </p>


                            <h4 class="fw-bold mb-3">
                                Precificação automatizada
                            </h4>


                            <p class="display-6 fw-bold mb-2">

                                <?= moeda($descontoExemplo) ?>

                            </p>


                            <span class="text-white-50">
                                Cálculo automático de desconto comercial
                                de 8%, evidenciando a aplicação de regras
                                de negócio.
                            </span>

                        </div>

                    </div>


                </div>

            </div>


        </div>

    </div>

</section>


<?php include __DIR__ . '/../includes/footer.php'; ?>