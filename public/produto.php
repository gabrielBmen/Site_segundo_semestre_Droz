<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

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
        p.permite_pedido,
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
                    <?php if ((bool) $produto['permite_pedido']): ?>
                        <div class="small text-white-50">Preço</div>
                        <div class="display-5 fw-bold"><?= moeda($produto['preco']) ?></div>
                        <div class="text-white-50 mt-2">
                            Estoque disponível: <?= (int) $produto['estoque'] ?>
                        </div>
                    <?php else: ?>
                        <div class="small text-white-50">Atendimento</div>
                        <div class="display-5 fw-bold">Sob orçamento</div>
                        <div class="text-white-50 mt-2">
                            Solicite uma proposta personalizada para esta solução.
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($adminLogado): ?>
                    <div class="alert alert-secondary rounded-4 border-0 mb-3">
                        Você está visualizando este item como administrador. Pedidos e orçamentos ficam disponíveis apenas para clientes.
                    </div>
                    <a href="/admin/produtos.php" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-pencil-square me-1"></i> Gerenciar produto
                    </a>
                <?php elseif ((bool) $produto['permite_pedido'] && (int) $produto['estoque'] > 0): ?>
                    <div class="d-flex flex-wrap align-items-end gap-2">
                        <form method="POST" action="carrinho.php" class="d-flex flex-wrap align-items-end gap-2">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="acao" value="adicionar">
                            <input type="hidden" name="id_produto" value="<?= (int) $produto['id_produto'] ?>">
                            <div>
                                <label for="quantidade" class="form-label small text-white-50 mb-1">Quantidade</label>
                                <input id="quantidade" name="quantidade" type="number" class="form-control" min="1" max="<?= (int) $produto['estoque'] ?>" value="1">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-cart-plus me-1"></i> Adicionar ao pedido
                            </button>
                        </form>
                        <a href="produtos.php" class="btn btn-outline-light btn-lg px-4">
                            Voltar ao catálogo
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info rounded-4 border-0 mb-3">
                        Esta solução é atendida por orçamento personalizado.
                    </div>
                    <a href="contato.php?produto_id=<?= (int) $produto['id_produto'] ?>" class="btn btn-primary btn-lg px-4">
                        Solicitar orçamento
                    </a>
                    <a href="produtos.php" class="btn btn-outline-light btn-lg px-4 ms-2">
                        Voltar ao catálogo
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
