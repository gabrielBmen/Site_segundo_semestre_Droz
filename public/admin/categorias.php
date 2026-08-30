<?php

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/CategoriaModel.php';
require_once __DIR__ . '/../../controllers/CategoriaController.php';

exigirAdmin();
$controller = new CategoriaController(new CategoriaModel($pdo));

$erro = '';
$sucesso = (string) ($_GET['sucesso'] ?? '');
$categoriaEdicao = [
    'id_categoria' => '',
    'nome' => '',
    'ativo' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } else {
        $acao = (string) ($_POST['acao'] ?? '');

        try {
            $idCategoria = (int) ($_POST['id_categoria'] ?? 0);
            if ($acao === 'salvar') {
                $resultado = $controller->salvar(
                    $idCategoria,
                    (string) ($_POST['nome'] ?? ''),
                    isset($_POST['ativo']) && $_POST['ativo'] === '1'
                );
            } elseif ($acao === 'alternar_status') {
                $resultado = $controller->alternarStatus($idCategoria);
            } elseif ($acao === 'excluir') {
                $resultado = $controller->excluir($idCategoria);
            } else {
                $resultado = ['sucesso' => false, 'mensagem' => 'Ação inválida.'];
            }

            if ($resultado['sucesso']) {
                header('Location: categorias.php?sucesso=' . rawurlencode((string) $resultado['mensagem']));
                exit;
            }
            $erro = (string) $resultado['mensagem'];
        } catch (Throwable $e) {
            $erro = 'Não foi possível concluir a operação. Tente novamente.';
        }
    }
}

if (isset($_GET['editar'])) {
    $encontrada = $controller->buscar((int) $_GET['editar']);

    if ($encontrada) {
        $categoriaEdicao = $encontrada;
    } else {
        $erro = 'Categoria não encontrada.';
    }
}

$categorias = $controller->listar();

$adminPagina = 'Categorias';
include __DIR__ . '/includes/header.php';
?>

<?php if ($sucesso !== ''): ?>
    <div class="alert alert-success"><?= e($sucesso) ?></div>
<?php endif; ?>

<?php if ($erro !== ''): ?>
    <div class="alert alert-danger"><?= e($erro) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="dashboard-card">
            <div class="text-uppercase small text-white-50">Cadastro</div>
            <h2 class="h5 mb-3"><?= $categoriaEdicao['id_categoria'] !== '' ? 'Editar categoria' : 'Nova categoria' ?></h2>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id_categoria" value="<?= (int) $categoriaEdicao['id_categoria'] ?>">

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome da categoria</label>
                    <input id="nome" name="nome" class="form-control bg-dark text-white border-secondary" maxlength="80" value="<?= e($categoriaEdicao['nome']) ?>" required autofocus>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1" <?= (bool) $categoriaEdicao['ativo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ativo">Categoria ativa</label>
                    <div class="form-text text-white-50">Apenas categorias ativas aparecem no cadastro de novos produtos.</div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-1"></i><?= $categoriaEdicao['id_categoria'] !== '' ? 'Salvar alterações' : 'Criar categoria' ?>
                    </button>
                    <?php if ($categoriaEdicao['id_categoria'] !== ''): ?>
                        <a href="categorias.php" class="btn btn-outline-light">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <div class="text-uppercase small text-white-50">Organização do catálogo</div>
                    <h2 class="h5 mb-0">Categorias cadastradas</h2>
                </div>
                <span class="badge text-bg-secondary"><?= count($categorias) ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-dark align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Produtos</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$categorias): ?>
                            <tr><td colspan="4" class="text-white-50">Nenhuma categoria cadastrada.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($categoria['nome']) ?></div>
                                    <small class="text-white-50">#<?= (int) $categoria['id_categoria'] ?></small>
                                </td>
                                <td><?= (int) $categoria['total_produtos'] ?></td>
                                <td>
                                    <span class="badge <?= (bool) $categoria['ativo'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                        <?= (bool) $categoria['ativo'] ? 'Ativa' : 'Inativa' ?>
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="categorias.php?editar=<?= (int) $categoria['id_categoria'] ?>" class="btn btn-sm btn-outline-light" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="acao" value="alternar_status">
                                        <input type="hidden" name="id_categoria" value="<?= (int) $categoria['id_categoria'] ?>">
                                        <button type="submit" class="btn btn-sm <?= (bool) $categoria['ativo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="<?= (bool) $categoria['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                            <i class="bi <?= (bool) $categoria['ativo'] ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                        </button>
                                    </form>
                                    <?php if ((int) $categoria['total_produtos'] === 0): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Excluir esta categoria?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id_categoria" value="<?= (int) $categoria['id_categoria'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
