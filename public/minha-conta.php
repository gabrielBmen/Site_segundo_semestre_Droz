<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../models/PedidoModel.php';

exigirLogin('minha-conta.php');

$usuarioSessao = usuarioAtual() ?? [];
$idUsuario = (int) ($usuarioSessao['id_usuario'] ?? 0);
$usuarioModel = new UsuarioModel($pdo);
$usuarioController = new UsuarioController($usuarioModel);
$pedidoModel = new PedidoModel($pdo);
$erro = '';
$sucesso = (string) ($_GET['sucesso'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } else {
        $resultado = $usuarioController->atualizarProprioPerfil(
            $idUsuario,
            (string) ($_POST['telefone'] ?? ''),
            (string) ($_POST['cep'] ?? ''),
            isset($_FILES['foto_perfil']) && is_array($_FILES['foto_perfil']) ? $_FILES['foto_perfil'] : null,
            isset($_POST['remover_foto']) && $_POST['remover_foto'] === '1'
        );

        if ($resultado['sucesso']) {
            header('Location: minha-conta.php?sucesso=' . rawurlencode((string) $resultado['mensagem']));
            exit;
        }
        $erro = (string) $resultado['mensagem'];
    }
}

$perfil = $usuarioModel->buscarPerfilPorId($idUsuario);
if (!$perfil) {
    http_response_code(404);
    exit('Perfil não encontrado.');
}

$pedidos = $perfil['tipo'] === 'cliente' ? $pedidoModel->listarPorUsuario($idUsuario) : [];
$fotoPerfil = (string) ($perfil['foto_perfil'] ?? '');
$fotoUrl = str_starts_with($fotoPerfil, 'uploads/perfis/') ? '/' . $fotoPerfil : '';
$partesNome = preg_split('/\s+/', trim((string) $perfil['nome'])) ?: [];
$iniciais = '';
foreach (array_slice($partesNome, 0, 2) as $parteNome) {
    $iniciais .= mb_strtoupper(mb_substr($parteNome, 0, 1));
}
$iniciais = $iniciais !== '' ? $iniciais : 'DR';

$statusLabels = [
    'pendente' => 'Pendente',
    'aprovado' => 'Aprovado',
    'concluido' => 'Concluído',
    'cancelado' => 'Cancelado',
];
$statusClasses = [
    'pendente' => 'text-bg-warning',
    'aprovado' => 'text-bg-info',
    'concluido' => 'text-bg-success',
    'cancelado' => 'text-bg-secondary',
];

$tituloPagina = 'Minha conta';
$descricaoPagina = 'Dados privados e histórico de pedidos da sua conta DROZ Robótica.';
$paginaAtiva = 'conta';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5 account-page">
    <div class="container">
        <?php if ($sucesso !== ''): ?>
            <div class="alert alert-success rounded-4 border-0"><?= e($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger rounded-4 border-0"><?= e($erro) ?></div>
        <?php endif; ?>

        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <div class="glass-card rounded-4 p-4 h-100 account-profile-card">
                    <div class="account-avatar account-avatar-large mx-auto mb-3">
                        <?php if ($fotoUrl !== ''): ?>
                            <img src="<?= e($fotoUrl) ?>" alt="Foto de perfil de <?= e($perfil['nome']) ?>">
                        <?php else: ?>
                            <span><?= e($iniciais) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-center mb-4">
                        <span class="badge-soft d-inline-flex mb-2"><?= $perfil['tipo'] === 'admin' ? 'Administrador' : 'Cliente' ?></span>
                        <h1 class="h3 fw-bold mb-1"><?= e($perfil['nome']) ?></h1>
                        <p class="text-white-50 mb-0"><?= e($perfil['email']) ?></p>
                    </div>

                    <div class="account-private-note mb-4">
                        <i class="bi bi-shield-lock"></i>
                        <div>
                            <strong>Área privada</strong>
                            <small>Estes dados são carregados exclusivamente pela sua sessão autenticada.</small>
                        </div>
                    </div>

                    <div class="account-data-list">
                        <div><span>Conta criada em</span><strong><?= e(date('d/m/Y', strtotime($perfil['data_criacao']))) ?></strong></div>
                        <div><span>Tipo de acesso</span><strong><?= $perfil['tipo'] === 'admin' ? 'Administrador' : 'Cliente' ?></strong></div>
                        <?php if ($perfil['tipo'] === 'cliente'): ?>
                            <div><span>Telefone</span><strong><?= e($perfil['telefone'] ?: 'Não informado') ?></strong></div>
                            <div><span>CEP</span><strong><?= e($perfil['cep'] ?: 'Não informado') ?></strong></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card rounded-4 p-4 p-lg-5 h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <span class="badge-soft d-inline-flex mb-2"><i class="bi bi-person-gear me-2"></i>Dados pessoais</span>
                            <h2 class="h3 fw-bold mb-1">Gerenciar perfil</h2>
                            <p class="text-white-50 mb-0">Atualize somente informações da sua própria conta.</p>
                        </div>
                        <?php if ($perfil['tipo'] === 'admin'): ?>
                            <a href="/admin/" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i>Abrir dashboard</a>
                        <?php endif; ?>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input class="form-control" value="<?= e($perfil['nome']) ?>" readonly aria-readonly="true">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-mail</label>
                                <input class="form-control" value="<?= e($perfil['email']) ?>" readonly aria-readonly="true">
                            </div>
                            <?php if ($perfil['tipo'] === 'cliente'): ?>
                                <div class="col-md-6">
                                    <label for="telefonePerfil" class="form-label">Telefone</label>
                                    <input id="telefonePerfil" name="telefone" type="tel" class="form-control" maxlength="30" autocomplete="tel" value="<?= e($perfil['telefone'] ?? '') ?>" placeholder="(44) 99999-9999" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cepPerfil" class="form-label">CEP</label>
                                    <input id="cepPerfil" name="cep" type="text" class="form-control" inputmode="numeric" maxlength="9" value="<?= e($perfil['cep'] ?? '') ?>" placeholder="00000-000" required>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label for="fotoPerfil" class="form-label">Foto de perfil</label>
                                <input id="fotoPerfil" name="foto_perfil" type="file" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text text-white-50">JPG, PNG ou WEBP, com até 2 MB.</div>
                            </div>
                            <?php if ($fotoUrl !== ''): ?>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input id="removerFoto" name="remover_foto" value="1" type="checkbox" class="form-check-input">
                                        <label for="removerFoto" class="form-check-label">Remover a foto atual</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check2-circle me-1"></i>Salvar alterações</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($perfil['tipo'] === 'cliente'): ?>
            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <div class="glass-card rounded-4 p-4 account-metric h-100">
                        <i class="bi bi-receipt"></i>
                        <div><span>Pedidos realizados</span><strong><?= count($pedidos) ?></strong></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card rounded-4 p-4 account-metric h-100">
                        <i class="bi bi-cash-stack"></i>
                        <div><span>Valor em pedidos não cancelados</span><strong><?= moeda((float) $perfil['valor_pedidos']) ?></strong></div>
                    </div>
                </div>
            </div>

            <div id="historico-pedidos" class="glass-card rounded-4 p-4 p-lg-5 mt-4 scroll-mt-nav">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <span class="badge-soft d-inline-flex mb-2"><i class="bi bi-clock-history me-2"></i>Histórico privado</span>
                        <h2 class="h3 fw-bold mb-1">Meus pedidos</h2>
                        <p class="text-white-50 mb-0">Acompanhe itens, datas, valores e situação de cada pedido.</p>
                    </div>
                    <span class="badge text-bg-secondary"><?= count($pedidos) ?> pedido(s)</span>
                </div>

                <?php if (!$pedidos): ?>
                    <div class="account-empty-state text-center py-5">
                        <i class="bi bi-bag-x display-5"></i>
                        <h3 class="h5 mt-3">Você ainda não realizou pedidos</h3>
                        <p class="text-white-50">Produtos disponíveis para compra podem ser adicionados pelo catálogo.</p>
                        <a href="produtos.php" class="btn btn-primary">Explorar catálogo</a>
                    </div>
                <?php else: ?>
                    <div class="accordion account-orders" id="accordionPedidos">
                        <?php foreach ($pedidos as $indice => $pedido): ?>
                            <?php $collapseId = 'pedido-' . (int) $pedido['id_pedido']; ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button <?= $indice > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($collapseId) ?>" aria-expanded="<?= $indice === 0 ? 'true' : 'false' ?>" aria-controls="<?= e($collapseId) ?>">
                                        <span class="order-number">Pedido #<?= (int) $pedido['id_pedido'] ?></span>
                                        <span class="order-date"><?= e(date('d/m/Y H:i', strtotime($pedido['data_pedido']))) ?></span>
                                        <span class="badge <?= e($statusClasses[$pedido['status']] ?? 'text-bg-secondary') ?>"><?= e($statusLabels[$pedido['status']] ?? ucfirst($pedido['status'])) ?></span>
                                        <strong><?= moeda($pedido['valor_total']) ?></strong>
                                    </button>
                                </h3>
                                <div id="<?= e($collapseId) ?>" class="accordion-collapse collapse <?= $indice === 0 ? 'show' : '' ?>" data-bs-parent="#accordionPedidos">
                                    <div class="accordion-body">
                                        <?php foreach ($pedido['itens'] as $item): ?>
                                            <div class="order-item-row">
                                                <div>
                                                    <strong><?= e($item['produto']) ?></strong>
                                                    <small><?= (int) $item['quantidade'] ?> × <?= moeda($item['preco_unitario']) ?></small>
                                                </div>
                                                <strong><?= moeda($item['subtotal']) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="order-total-row"><span>Total do pedido</span><strong><?= moeda($pedido['valor_total']) ?></strong></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
