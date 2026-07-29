<?php
$tituloPagina = 'Produtos';
$descricaoPagina = 'Catálogo de produtos e soluções da DROZ Robótica.';
$paginaAtiva = 'produtos';

require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/config/conexao.php'; // INCLUSÃO ADICIONADA: Atendendo à recuperação de dados reais do BD
include __DIR__ . '/includes/header.php';

$termo = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');

$lista = $catalogoProdutos;

if ($termo !== '') {
    $lista = array_filter($lista, function ($produto) use ($termo) {
        $texto = strtolower($produto['nome'] . ' ' . $produto['descricao'] . ' ' . $produto['categoria']);
        return str_contains($texto, mb_strtolower($termo));
    });
}

if ($categoria !== '') {
    $lista = array_filter($lista, function ($produto) use ($categoria) {
        return strtolower($produto['categoria']) === strtolower($categoria);
    });
}

$categorias = [];
foreach ($catalogoProdutos as $item) {
    $categorias[] = $item['categoria'];
}
$categorias = array_values(array_unique($categorias));

$destaquesFiltrados = buscarProdutoPorSlug($catalogoProdutos, 'celula-robotizada-csr1');
?>
<link rel="stylesheet" href="assets/css/style.css?v=2">
<section class="py-5">
    <div class="container">
        <div class="row align-items-end g-3 mb-4">
            <div class="col-lg-7">
                <h1 class="section-title">Catálogo de soluções</h1>
                <p class="section-subtitle mb-0">Página dinâmica com busca, filtro por categoria e recuperação de dados para demonstrar o funcionamento do banco e da lógica do projeto.</p>
            </div>
            <div class="col-lg-5">
                <form method="get" class="glass-card rounded-4 p-3">
                    <div class="row g-2">
                        <div class="col-md-7">
                            <input type="text" name="q" value="<?= e($termo) ?>" class="form-control" placeholder="Buscar produto...">
                        </div>
                        <div class="col-md-3">
                            <select name="categoria" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= e($cat) ?>" <?= $categoria === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($lista as $produto): ?>
            <div class="col-lg-4 col-md-6" id="<?= e($produto['slug']) ?>">
                <article class="product-card">
                    <img src="<?= e($produto['imagem']) ?>" alt="<?= e($produto['nome']) ?>">
                    <div class="product-body">
                        <span class="badge badge-category mb-2"><?= e($produto['categoria']) ?></span>
                        <h5 class="fw-bold"><?= e($produto['nome']) ?></h5>
                        <p class="small-muted"><?= e($produto['descricao']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <div class="price"><?= moeda($produto['preco']) ?></div>
                                <small class="text-white-50">Estoque: <?= (int)$produto['estoque'] ?></small>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="tooltip" title="Solicite proposta comercial">Comprar</button>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>

            <?php if (empty($lista)): ?>
                <div class="col-12">
                    <div class="glass-card rounded-4 p-5 text-center">
                        <h4 class="fw-bold">Nenhum resultado encontrado</h4>
                        <p class="text-white-50 mb-0">Tente outro termo de busca ou remova o filtro de categoria.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($destaquesFiltrados): ?>
        <div class="row mt-5">
            <div class="col-lg-12">
                <div class="info-card rounded-4 p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-2">Destaque do catálogo</h4>
                            <p class="text-white-50 mb-0">A CSR1 aparece como solução principal para soldagem robotizada, alinhada ao posicionamento público da empresa.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="display-6 fw-bold"><?= moeda($destaquesFiltrados['preco']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>