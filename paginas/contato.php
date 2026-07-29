<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/config/conexao.php';

$tituloPagina = 'Contato';
$descricaoPagina = 'Fale com a DROZ Robótica e solicite orçamento para automação industrial e robótica.';
$paginaAtiva = 'contato';

$mensagemEnviada = false;
$erro = '';

$nome = '';
$email = '';
$telefone = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $mensagem !== '') {
        try {
            // CORREÇÃO: Adicionado o campo 'mensagem' e o placeholder ':mensagem' na consulta SQL
            $sql = "INSERT INTO clientes (nome, email, telefone, mensagem) VALUES (:nome, :email, :telefone, :mensagem)";
            $stmt = $pdo->prepare($sql);

            if ($stmt) {
                // CORREÇÃO: Vinculado o valor da variável $mensagem ao placeholder ':mensagem'
                $executou = $stmt->execute([
                    ':nome'     => $nome,
                    ':email'    => $email,
                    ':telefone' => $telefone,
                    ':mensagem' => $mensagem 
                ]);

                if ($executou) {
                    $mensagemEnviada = true;
                    $nome = '';
                    $email = '';
                    $telefone = '';
                    $mensagem = '';
                } else {
                    $erro = 'Erro ao salvar no banco de dados.';
                }
            } else {
                $erro = 'Erro ao preparar o envio da mensagem.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro no processamento do banco: ' . $e->getMessage();
        }
    } else {
        $erro = 'Preencha os campos obrigatórios corretamente.';
    }
}

include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="assets/css/style.css?v=2">
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
                                Entre em contato conosco para soluções em automação industrial e robótica.
                            </p>

                            <div class="info-card rounded-4 p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle flex-shrink-0"><i class="bi bi-geo-alt"></i></div>
                                    <div class="fw-semibold mb-0">Campo Mourão - PR</div>
                                </div>

                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icon-circle flex-shrink-0"><i class="bi bi-envelope"></i></div>
                                    <div>
                                        <a class="text-white text-decoration-none mb-0" href="mailto:contato@drozrobotica.com.br">
                                            drozrobotica@drozrobotica.com.br
                                        </a>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle flex-shrink-0"><i class="bi bi-telephone"></i></div>
                                    <div>
                                        <a class="text-white text-decoration-none mb-0" href="tel:+5544999450050">
                                            +55 (44) 99945-0050
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <?php if ($mensagemEnviada): ?>
                                <div class="alert alert-success rounded-4 border-0">
                                    Mensagem enviada com sucesso!
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
                                    <input
                                        type="text"
                                        name="nome"
                                        class="form-control"
                                        value="<?= e($nome) ?>"
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        value="<?= e($email) ?>"
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Telefone</label>
                                    <input
                                        type="text"
                                        name="telefone"
                                        class="form-control"
                                        value="<?= e($telefone) ?>"
                                    >
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Mensagem</label>
                                    <textarea
                                        name="mensagem"
                                        class="form-control"
                                        rows="6"
                                        required
                                    ><?= e($mensagem) ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    Enviar Mensagem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>