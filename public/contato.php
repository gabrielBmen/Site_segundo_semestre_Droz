<?php

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

$urlAtual = 'contato.php';
exigirLogin($urlAtual);

if (usuarioAdmin()) {
    header('Location: /admin/orcamentos.php');
    exit;
}

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

$produtosOrcamento = $pdo->query(
    'SELECT p.id_produto, p.nome, p.permite_pedido, c.nome AS categoria
     FROM produtos p
     INNER JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE p.ativo = TRUE
     ORDER BY c.nome ASC, p.nome ASC'
)->fetchAll();

$mensagemEnviada = false;
$erro = '';

$nome = $cliente['nome'];
$email = $cliente['email'];
$telefone = $cliente['telefone'] ?? '';
$idProduto = (int) ($_GET['produto_id'] ?? 0);
$produtoSolicitado = null;

if ($idProduto > 0) {
    $stmtProduto = $pdo->prepare(
        'SELECT id_produto, nome FROM produtos
         WHERE id_produto = :id_produto AND ativo = TRUE LIMIT 1'
    );
    $stmtProduto->execute([':id_produto' => $idProduto]);
    $produtoSolicitado = $stmtProduto->fetch();
    $idProduto = $produtoSolicitado ? (int) $produtoSolicitado['id_produto'] : 0;
}

$interesse = $produtoSolicitado['nome'] ?? '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $interesse = trim($_POST['interesse'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $idProduto = (int) ($_POST['id_produto'] ?? 0);

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif ($idProduto > 0) {
        $stmtProduto = $pdo->prepare(
            'SELECT id_produto, nome FROM produtos
             WHERE id_produto = :id_produto AND ativo = TRUE LIMIT 1'
        );
        $stmtProduto->execute([':id_produto' => $idProduto]);
        $produtoSolicitado = $stmtProduto->fetch();

        if (!$produtoSolicitado) {
            $erro = 'O produto informado não está mais disponível para orçamento.';
            $idProduto = 0;
        }
    }

    if ($erro === '') {
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $mensagem === '') {
            $erro = 'Preencha nome, e-mail e mensagem corretamente.';
        } else {
            try {
            $sql = "
                INSERT INTO contatos (
                    id_cliente,
                    id_produto,
                    nome,
                    email,
                    telefone,
                    interesse,
                    mensagem
                ) VALUES (
                    :id_cliente,
                    :id_produto,
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
                ':id_produto' => $idProduto > 0 ? $idProduto : null,
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone !== '' ? $telefone : null,
                ':interesse' => $interesse !== '' ? $interesse : null,
                ':mensagem' => $mensagem,
            ]);

                $mensagemEnviada = true;
                $idProduto = 0;
                $produtoSolicitado = null;
                $interesse = '';
                $mensagem = '';
            } catch (PDOException $e) {
                $erro = 'Não foi possível salvar sua solicitação.';
            }
        }
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
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
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
                                    <label for="produtoInteresse" class="form-label">Produto de interesse</label>
                                    <div class="categoria-dropdown" data-produto-dropdown>
                                        <input type="hidden" name="id_produto" value="<?= $idProduto > 0 ? (int) $idProduto : '' ?>" data-produto-value>
                                        <button
                                            id="produtoInteresse"
                                            type="button"
                                            class="categoria-dropdown-toggle"
                                            data-produto-toggle
                                            aria-expanded="false"
                                            aria-haspopup="listbox"
                                        >
                                            <span data-produto-label><?= e($produtoSolicitado['nome'] ?? 'Assunto geral / ainda não sei o produto') ?></span>
                                            <span class="dropdown-arrow" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                                        </button>
                                        <div class="categoria-dropdown-menu" data-produto-menu role="listbox">
                                            <button
                                                type="button"
                                                class="categoria-dropdown-option <?= $idProduto === 0 ? 'is-selected' : '' ?>"
                                                data-produto-option
                                                data-value=""
                                                data-label="Assunto geral / ainda não sei o produto"
                                                role="option"
                                                aria-selected="<?= $idProduto === 0 ? 'true' : 'false' ?>"
                                            >
                                                Assunto geral / ainda não sei o produto
                                            </button>
                                            <?php foreach ($produtosOrcamento as $produtoOpcao): ?>
                                                <?php $produtoOpcaoId = (int) $produtoOpcao['id_produto']; ?>
                                                <button
                                                    type="button"
                                                    class="categoria-dropdown-option <?= $produtoOpcaoId === $idProduto ? 'is-selected' : '' ?>"
                                                    data-produto-option
                                                    data-value="<?= $produtoOpcaoId ?>"
                                                    data-label="<?= e($produtoOpcao['nome']) ?>"
                                                    role="option"
                                                    aria-selected="<?= $produtoOpcaoId === $idProduto ? 'true' : 'false' ?>"
                                                    title="<?= e($produtoOpcao['nome'] . ' — ' . $produtoOpcao['categoria']) ?>"
                                                >
                                                    <?= e($produtoOpcao['nome']) ?> — <?= e($produtoOpcao['categoria']) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="form-text text-white-50">Ao selecionar um item, ele aparecerá identificado na área administrativa.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Interesse ou aplicação</label>
                                    <input type="text" name="interesse" class="form-control" value="<?= e($interesse) ?>" placeholder="Ex.: soldagem, automação, treinamento...">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.querySelector('[data-produto-dropdown]');
    if (!dropdown) return;

    const toggle = dropdown.querySelector('[data-produto-toggle]');
    const hiddenInput = dropdown.querySelector('[data-produto-value]');
    const label = dropdown.querySelector('[data-produto-label]');
    const options = dropdown.querySelectorAll('[data-produto-option]');
    if (!toggle || !hiddenInput || !label || options.length === 0) return;

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
            label.textContent = option.dataset.label || option.textContent.trim();

            options.forEach((item) => {
                const selected = item === option;
                item.classList.toggle('is-selected', selected);
                item.setAttribute('aria-selected', selected ? 'true' : 'false');
            });

            closeDropdown();
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Node && !dropdown.contains(event.target)) closeDropdown();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
            toggle.focus();
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
