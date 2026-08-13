<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

$urlAtual = 'contato.php';
exigirLogin($urlAtual);

$tituloPagina = 'Contato';
$descricaoPagina = 'Fale com a DROZ Robótica e solicite orçamento para automação industrial e robótica.';
$paginaAtiva = 'contato';

$usuario = usuarioAtual();
$usuarioModel = new UsuarioModel($pdo);
$cliente = $usuarioModel->buscarClientePorUsuario((int) $usuario['id_usuario']);

if (!$cliente) {
    http_response_code(403);
    exit('Cadastro de cliente não encontrado.');
}

$mensagemEnviada = false;
$erro = '';

$nome = $cliente['nome'];
$email = $cliente['email'];
$telefone = $cliente['telefone'] ?? '';
$interesse = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $interesse = trim($_POST['interesse'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $mensagem !== '') {
        try {
            $sql = "
                INSERT INTO contatos (
                    id_cliente,
                    nome,
                    email,
                    telefone,
                    interesse,
                    mensagem
                ) VALUES (
                    :id_cliente,
                    :nome,
                    :email,
                    :telefone,
                    :interesse,
                    :mensagem
                )
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_cliente' => $cliente['id_cliente'],
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone !== '' ? $telefone : null,
                ':interesse' => $interesse !== '' ? $interesse : null,
                ':mensagem' => $mensagem,
            ]);

            $mensagemEnviada = true;
            $interesse = '';
            $mensagem = '';
        } catch (PDOException $e) {
            $erro = 'Não foi possível salvar sua solicitação.';
        }
    } else {
        $erro = 'Preencha nome, e-mail e mensagem corretamente.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card rounded-4 p-4 p-lg-5">
                    <div class="row g-5 align-items-start">
                        <div class="col-lg-5">
                            <span class="badge-soft mb-3 d-inline-flex">
                                <i class="bi bi-chat-dots me-2"></i>Contato direto
                            </span>

                            <h1 class="hero-title mb-3" style="font-size: clamp(2rem, 4vw, 3.2rem);">
                                Fale com a<br>DROZ Robótica
                            </h1>

                            <p class="section-subtitle mb-4">
                                Sua conta já está autenticada. Envie uma solicitação de orçamento ou tire dúvidas sobre uma máquina.
                            </p>

                            <div class="info-card rounded-4 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle flex-shrink-0"><i class="bi bi-person"></i></div>
                                    <div class="fw-semibold mb-0"><?= e($cliente['nome']) ?></div>
                                </div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle flex-shrink-0"><i class="bi bi-envelope"></i></div>
                                    <div><?= e($cliente['email']) ?></div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle flex-shrink-0"><i class="bi bi-geo-alt"></i></div>
                                    <div>Campo Mourão - PR</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <?php if ($mensagemEnviada): ?>
                                <div class="alert alert-success rounded-4 border-0">
                                    Solicitação enviada com sucesso!
                                </div>
                            <?php endif; ?>

                            <?php if ($erro): ?>
                                <div class="alert alert-danger rounded-4 border-0">
                                    <?= e($erro) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="contact-form">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" class="form-control" value="<?= e($nome) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Telefone</label>
                                    <input type="text" name="telefone" class="form-control" value="<?= e($telefone) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Interesse</label>
                                    <input type="text" name="interesse" class="form-control" value="<?= e($interesse) ?>" placeholder="Ex.: CSR1, automação, treinamento...">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Mensagem</label>
                                    <textarea name="mensagem" class="form-control" rows="6" required><?= e($mensagem) ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    Enviar solicitação
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
