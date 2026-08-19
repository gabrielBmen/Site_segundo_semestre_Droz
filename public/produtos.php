<?php

$tituloPagina = 'Produtos';
$descricaoPagina = 'Catálogo de produtos e soluções da DROZ Robótica.';
$paginaAtiva = 'produtos';


/*
|--------------------------------------------------------------------------
| ARQUIVOS NECESSÁRIOS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';


/*
|--------------------------------------------------------------------------
| BUSCAR PRODUTOS NO BANCO
|--------------------------------------------------------------------------
|
| Aqui o banco de dados passa a ser a fonte oficial
| dos produtos do site.
|
*/

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


/*
|--------------------------------------------------------------------------
| TRANSFORMAR RESULTADO DO BANCO EM ARRAY
|--------------------------------------------------------------------------
*/

$catalogoProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VALIDAR PRODUTOS
|--------------------------------------------------------------------------
*/

if (!validarListaProdutos($catalogoProdutos)) {

    $catalogoProdutos = [];
}


/*
|--------------------------------------------------------------------------
| FILTROS RECEBIDOS PELA URL
|--------------------------------------------------------------------------
|
| Exemplo:
|
| produtos.php?q=robo
|
| ou:
|
| produtos.php?categoria=Soldagem%20robotizada
|
*/

$termo = trim(
    $_GET['q'] ?? ''
);

$categoria = trim(
    $_GET['categoria'] ?? ''
);


/*
|--------------------------------------------------------------------------
| LISTA QUE SERÁ EXIBIDA
|--------------------------------------------------------------------------
*/

$lista = $catalogoProdutos;


/*
|--------------------------------------------------------------------------
| FILTRO POR TEXTO
|--------------------------------------------------------------------------
*/

if ($termo !== '') {

    $termoBusca = mb_strtolower(
        $termo,
        'UTF-8'
    );

    $lista = array_filter(
        $lista,
        function ($produto) use ($termoBusca) {

            $texto = mb_strtolower(
                (
                    ($produto['nome'] ?? '') . ' ' .
                    ($produto['descricao'] ?? '') . ' ' .
                    ($produto['categoria'] ?? '')
                ),
                'UTF-8'
            );

            return str_contains(
                $texto,
                $termoBusca
            );
        }
    );
}


/*
|--------------------------------------------------------------------------
| FILTRO POR CATEGORIA
|--------------------------------------------------------------------------
*/

if ($categoria !== '') {

    $categoriaBusca = mb_strtolower(
        $categoria,
        'UTF-8'
    );

    $lista = array_filter(
        $lista,
        function ($produto) use ($categoriaBusca) {

            return mb_strtolower(
                $produto['categoria'] ?? '',
                'UTF-8'
            ) === $categoriaBusca;
        }
    );
}


/*
|--------------------------------------------------------------------------
| BUSCAR CATEGORIAS
|--------------------------------------------------------------------------
|
| Por enquanto estamos montando as opções a partir
| dos produtos encontrados no banco.
|
*/

$categorias = [];

foreach ($catalogoProdutos as $produto) {

    if (
        isset($produto['categoria']) &&
        $produto['categoria'] !== ''
    ) {

        $categorias[] = $produto['categoria'];
    }
}


/*
|--------------------------------------------------------------------------
| REMOVER CATEGORIAS DUPLICADAS
|--------------------------------------------------------------------------
*/

$categorias = array_values(
    array_unique($categorias)
);


/*
|--------------------------------------------------------------------------
| PRODUTO DE DESTAQUE
|--------------------------------------------------------------------------
*/

$destaquesFiltrados = buscarProdutoPorSlug(
    $catalogoProdutos,
    'celula-robotizada-csr1'
);


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include __DIR__ . '/../includes/header.php';

?>


<link
    rel="stylesheet"
    href="assets/css/style.css?v=3"
>


<section class="py-5">

    <div class="container">

        <div class="row align-items-end g-3 mb-4">

            <div class="col-lg-7">

                <h1 class="section-title">
                    Catálogo de soluções
                </h1>

                <p class="section-subtitle mb-0">
                    Página dinâmica com busca, filtro por categoria
                    e produtos carregados diretamente do banco de dados.
                </p>

            </div>


            <div class="col-lg-5">

                <form
                    method="get"
                    class="glass-card rounded-4 p-3 catalogo-filtro"
                >

                    <div class="row g-2">

                        <div class="col-md-7">

                            <input
                                type="text"
                                name="q"
                                value="<?= e($termo) ?>"
                                class="form-control"
                                placeholder="Buscar produto..."
                            >

                        </div>


                        <div class="col-md-3">

                            <?php
                            $categoriaSelecionada = $categoria !== ''
                                ? $categoria
                                : 'Todas';
                            ?>

                            <div class="categoria-dropdown" data-categoria-dropdown>

                                <input
                                    type="hidden"
                                    name="categoria"
                                    value="<?= e($categoria) ?>"
                                    data-categoria-value
                                >

                                <button
                                    type="button"
                                    class="categoria-dropdown-toggle"
                                    data-categoria-toggle
                                    aria-expanded="false"
                                    aria-haspopup="listbox"
                                >
                                    <span data-categoria-label>
                                        <?= e($categoriaSelecionada) ?>
                                    </span>

                                    <span class="dropdown-arrow" aria-hidden="true">
                                        <i class="bi bi-chevron-down"></i>
                                    </span>
                                </button>

                                <div
                                    class="categoria-dropdown-menu"
                                    data-categoria-menu
                                    role="listbox"
                                >

                                    <button
                                        type="button"
                                        class="categoria-dropdown-option <?= $categoria === '' ? 'is-selected' : '' ?>"
                                        data-categoria-option
                                        data-value=""
                                        role="option"
                                        aria-selected="<?= $categoria === '' ? 'true' : 'false' ?>"
                                    >
                                        Todas
                                    </button>

                                    <?php foreach ($categorias as $cat): ?>

                                        <button
                                            type="button"
                                            class="categoria-dropdown-option <?= $categoria === $cat ? 'is-selected' : '' ?>"
                                            data-categoria-option
                                            data-value="<?= e($cat) ?>"
                                            role="option"
                                            aria-selected="<?= $categoria === $cat ? 'true' : 'false' ?>"
                                        >
                                            <?= e($cat) ?>
                                        </button>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-2 d-grid">

                            <button
                                type="submit"
                                class="btn btn-filtrar"
                            >
                                Filtrar
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <div class="row g-4">


            <?php foreach ($lista as $produto): ?>

                <div
                    class="col-lg-4 col-md-6"
                    id="<?= e($produto['slug']) ?>"
                >

                    <article class="product-card">


                        <?php if (!empty($produto['imagem'])): ?>

                            <img
                                src="<?= e($produto['imagem']) ?>"
                                alt="<?= e($produto['nome']) ?>"
                            >

                        <?php else: ?>

                            <div class="p-5 text-center">
                                Imagem não disponível
                            </div>

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


                            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">

                                <div>
                                    <?php if (usuarioLogado()): ?>
                                        <div class="price">
                                            <?= moeda($produto['preco']) ?>
                                        </div>
                                        <small class="text-white-50">
                                            Estoque: <?= (int) $produto['estoque'] ?>
                                        </small>
                                    <?php else: ?>
                                        <div class="price">
                                            <i class="bi bi-lock-fill me-1"></i> Preço protegido
                                        </div>
                                        <small class="text-white-50">
                                            Faça login para ver preço e detalhes.
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <a href="produto.php?slug=<?= rawurlencode($produto['slug']) ?>" class="btn btn-primary text-nowrap">
                                    <?= usuarioLogado() ? 'Ver detalhes' : 'Entrar para ver' ?>
                                </a>

                            </div>

                        </div>

                    </article>

                </div>

            <?php endforeach; ?>


            <?php if (empty($lista)): ?>

                <div class="col-12">

                    <div class="glass-card rounded-4 p-5 text-center">

                        <h4 class="fw-bold">
                            Nenhum resultado encontrado
                        </h4>

                        <p class="text-white-50 mb-0">
                            Tente outro termo de busca
                            ou remova o filtro de categoria.
                        </p>

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

                                <h4 class="fw-bold mb-2">
                                    Destaque do catálogo
                                </h4>

                                <p class="text-white-50 mb-0">
                                    A CSR1 aparece como solução principal
                                    para soldagem robotizada.
                                </p>

                            </div>


                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <?php if (usuarioLogado()): ?>
                                    <span class="display-6 fw-bold">
                                        <?= moeda($destaquesFiltrados['preco']) ?>
                                    </span>
                                <?php else: ?>
                                    <a href="produto.php?slug=<?= rawurlencode($destaquesFiltrados['slug']) ?>" class="btn btn-primary">
                                        Entrar para ver o preço
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>


    </div>

</section>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.querySelector('[data-categoria-dropdown]');

    if (!dropdown) return;

    const toggle = dropdown.querySelector('[data-categoria-toggle]');
    const hiddenInput = dropdown.querySelector('[data-categoria-value]');
    const label = dropdown.querySelector('[data-categoria-label]');
    const options = dropdown.querySelectorAll('[data-categoria-option]');

    const closeDropdown = () => {
        dropdown.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', (event) => {
        event.preventDefault();

        const isOpen = dropdown.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    options.forEach((option) => {
        option.addEventListener('click', () => {
            hiddenInput.value = option.dataset.value || '';
            label.textContent = option.textContent.trim();

            options.forEach((item) => {
                const selected = item === option;
                item.classList.toggle('is-selected', selected);
                item.setAttribute('aria-selected', selected ? 'true' : 'false');
            });

            closeDropdown();
        });
    });

    document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
            toggle.focus();
        }
    });
});
</script>


<?php

include __DIR__ . '/../includes/footer.php';

?>