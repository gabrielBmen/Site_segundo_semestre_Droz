<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$slug = trim($_GET['slug'] ?? '');
$urlAtual = 'produto.php?slug=' . rawurlencode($slug);

exigirLogin($urlAtual);

if ($slug === '') {
    header('Location: produtos.php');
    exit;
}

$sql = "
    SELECT
        p.id_produto,
        p.nome,
        p.slug,
        p.descricao,
        p.preco,
        p.estoque,
        c.nome AS categoria,
        i.caminho AS imagem
    FROM produtos p
    INNER JOIN categorias c
        ON c.id_categoria = p.id_categoria
    LEFT JOIN imagens_produto i
        ON i.id_produto = p.id_produto
        AND i.principal = TRUE
    WHERE p.slug = :slug
      AND p.ativo = TRUE
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':slug' => $slug]);
$produto = $stmt->fetch();

if (!$produto) {
    http_response_code(404);
    exit('Máquina não encontrada.');
}

$tituloPagina = $produto['nome'];
$descricaoPagina = $produto['descricao'] ?: 'Detalhes do produto DROZ Robótica.';
$paginaAtiva = 'produtos';

include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <?php if (!empty($produto['imagem'])): ?>
                    <img
                        src="<?= e($produto['imagem']) ?>"
                        alt="<?= e($produto['nome']) ?>"
                        class="img-fluid rounded-4 shadow"
                    >
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <span class="badge-soft mb-3 d-inline-flex">
                    <?= e($produto['categoria']) ?>
                </span>

                <h1 class="section-title mb-3"><?= e($produto['nome']) ?></h1>

                <p class="section-subtitle mb-4">
                    <?= e($produto['descricao']) ?>
                </p>

                <div class="info-card rounded-4 p-4 mb-4">
                    <div class="small text-white-50">Preço</div>
                    <div class="display-5 fw-bold"><?= moeda($produto['preco']) ?></div>
                    <div class="text-white-50 mt-2">
                        Estoque disponível: <?= (int) $produto['estoque'] ?>
                    </div>
                </div>

                <a href="contato.php" class="btn btn-primary btn-lg px-4">
                    Solicitar orçamento
                </a>

                <a href="produtos.php" class="btn btn-outline-light btn-lg px-4 ms-2">
                    Voltar ao catálogo
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
