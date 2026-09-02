<?php

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

exigirAdmin();

$controller = new UsuarioController(new UsuarioModel($pdo));
$adminAtual = usuarioAtual() ?? [];
$idAdminAtual = (int) ($adminAtual['id_usuario'] ?? 0);
$erro = '';
$sucesso = (string) ($_GET['sucesso'] ?? '');
$usuarioEdicao = [
    'id_usuario' => 0,
    'nome' => '',
    'email' => '',
    'telefone' => '',
    'cep' => '',
    'cpf' => '',
    'cnpj' => '',
    'tipo' => 'cliente',
    'ativo' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } else {
        $acao = (string) ($_POST['acao'] ?? '');
        try {
            if ($acao === 'salvar') {
                $resultado = $controller->salvarPeloAdmin($_POST, $idAdminAtual);
            } elseif ($acao === 'excluir') {
                $resultado = $controller->excluirPeloAdmin((int) ($_POST['id_usuario'] ?? 0), $idAdminAtual);
            } else {
                $resultado = ['sucesso' => false, 'mensagem' => 'Ação inválida.'];
            }

            if ($resultado['sucesso']) {
                header('Location: usuarios.php?sucesso=' . rawurlencode((string) $resultado['mensagem']));
                exit;
            }
            $erro = (string) $resultado['mensagem'];
            if ($acao === 'salvar') {
                $usuarioEdicao = array_merge($usuarioEdicao, $_POST);
            }
        } catch (Throwable $e) {
            $erro = 'Não foi possível concluir a operação. Tente novamente.';
        }
    }
}

if (isset($_GET['editar']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $encontrado = $controller->buscarParaAdmin((int) $_GET['editar']);
    if ($encontrado) {
        $usuarioEdicao = array_merge($usuarioEdicao, $encontrado);
    } else {
        $erro = 'Usuário não encontrado.';
    }
}

$usuarios = $controller->listarParaAdmin();
$adminPagina = 'Usuários';
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
            <h2 class="h5 mb-3"><?= (int) $usuarioEdicao['id_usuario'] > 0 ? 'Editar usuário' : 'Novo usuário' ?></h2>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id_usuario" value="<?= (int) $usuarioEdicao['id_usuario'] ?>">

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input id="nome" name="nome" class="form-control bg-dark text-white border-secondary" minlength="3" maxlength="120" value="<?= e((string) $usuarioEdicao['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input id="email" name="email" type="email" class="form-control bg-dark text-white border-secondary" maxlength="120" value="<?= e((string) $usuarioEdicao['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone do cliente</label>
                    <input id="telefone" name="telefone" class="form-control bg-dark text-white border-secondary" maxlength="30" value="<?= e((string) ($usuarioEdicao['telefone'] ?? '')) ?>" placeholder="(11) 99999-9999">
                </div>
                <div class="mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <input id="cep" name="cep" class="form-control bg-dark text-white border-secondary" inputmode="numeric" maxlength="9" value="<?= e((string) ($usuarioEdicao['cep'] ?? '')) ?>" placeholder="00000-000">
                    <div class="form-text text-white-50">Obrigatório para usuários do tipo cliente.</div>
                </div>
                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF do responsável</label>
                    <input id="cpf" name="cpf" class="form-control bg-dark text-white border-secondary" inputmode="numeric" maxlength="14" value="<?= e((string) ($usuarioEdicao['cpf'] ?? '')) ?>" placeholder="000.000.000-00">
                </div>
                <div class="mb-3">
                    <label for="cnpj" class="form-label">CNPJ da empresa</label>
                    <input id="cnpj" name="cnpj" class="form-control bg-dark text-white border-secondary" inputmode="numeric" maxlength="18" value="<?= e((string) ($usuarioEdicao['cnpj'] ?? '')) ?>" placeholder="00.000.000/0000-00">
                    <div class="form-text text-white-50">CPF e CNPJ são obrigatórios para usuários do tipo cliente.</div>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo de acesso</label>
                    <select id="tipo" name="tipo" class="form-select bg-dark text-white border-secondary" required>
                        <option value="cliente" <?= $usuarioEdicao['tipo'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                        <option value="admin" <?= $usuarioEdicao['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label"><?= (int) $usuarioEdicao['id_usuario'] > 0 ? 'Nova senha (opcional)' : 'Senha' ?></label>
                    <input id="senha" name="senha" type="password" class="form-control bg-dark text-white border-secondary" minlength="8" maxlength="72" <?= (int) $usuarioEdicao['id_usuario'] > 0 ? '' : 'required' ?> autocomplete="new-password">
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1" <?= (bool) $usuarioEdicao['ativo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ativo">Usuário ativo</label>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Salvar usuário</button>
                    <?php if ((int) $usuarioEdicao['id_usuario'] > 0): ?>
                        <a href="usuarios.php" class="btn btn-outline-light">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <div class="text-uppercase small text-white-50">Controle de acesso</div>
                    <h2 class="h5 mb-0">Usuários cadastrados</h2>
                </div>
                <span class="badge text-bg-secondary"><?= count($usuarios) ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-dark align-middle mb-0">
                    <thead><tr><th>Usuário</th><th>Tipo</th><th>Pedidos</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
                    <tbody>
                    <?php if (!$usuarios): ?>
                        <tr><td colspan="5" class="text-white-50">Nenhum usuário cadastrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e((string) $usuario['nome']) ?></div>
                                <small class="text-white-50"><?= e((string) $usuario['email']) ?> · #<?= (int) $usuario['id_usuario'] ?></small>
                            </td>
                            <td><span class="badge <?= $usuario['tipo'] === 'admin' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= e((string) $usuario['tipo']) ?></span></td>
                            <td><?= (int) $usuario['total_pedidos'] ?></td>
                            <td><span class="badge <?= (bool) $usuario['ativo'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (bool) $usuario['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                            <td class="text-end text-nowrap">
                                <a href="usuarios.php?editar=<?= (int) $usuario['id_usuario'] ?>" class="btn btn-sm btn-outline-light" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Excluir este usuário? Esta ação não pode ser desfeita.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir" <?= (int) $usuario['total_pedidos'] > 0 || (int) $usuario['id_usuario'] === $idAdminAtual ? 'disabled' : '' ?>><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="form-text text-white-50 mt-3 mb-0">Usuários com pedidos são preservados para manter o histórico. Nesses casos, use a opção de desativar.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
