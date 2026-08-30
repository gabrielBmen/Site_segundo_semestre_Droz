<?php

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/conexao.php';

exigirAdmin();

$stmt = $pdo->query(
    'SELECT ct.id_contato, ct.nome, ct.email, ct.telefone, ct.interesse, ct.mensagem, ct.data_contato,
            p.nome AS produto
     FROM contatos ct
     LEFT JOIN produtos p ON p.id_produto = ct.id_produto
     ORDER BY ct.data_contato DESC, ct.id_contato DESC'
);
$solicitacoes = $stmt->fetchAll();

function linkWhatsapp(?string $telefone, string $nome, ?string $interesse): ?string
{
    $numero = preg_replace('/\D+/', '', (string) $telefone);

    if (in_array(strlen($numero), [10, 11], true)) {
        $numero = '55' . $numero;
    }

    if (strlen($numero) < 12 || strlen($numero) > 15) {
        return null;
    }

    $texto = 'Olá, ' . $nome . '! Recebemos sua solicitação';
    if ($interesse) {
        $texto .= ' sobre ' . $interesse;
    }
    $texto .= '. Podemos conversar?';

    return 'https://wa.me/' . $numero . '?text=' . rawurlencode($texto);
}

$adminPagina = 'Orçamentos';
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="text-uppercase small text-white-50">Contato comercial</div>
            <h2 class="h5 mb-0">Solicitações de orçamento</h2>
        </div>
        <span class="badge text-bg-secondary"><?= count($solicitacoes) ?></span>
    </div>

    <div class="table-responsive">
        <table class="table table-dark align-middle mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Produto</th>
                    <th>Interesse</th>
                    <th>Mensagem</th>
                    <th class="text-end">Contato</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$solicitacoes): ?>
                    <tr><td colspan="6" class="text-white-50">Nenhuma solicitação de orçamento recebida.</td></tr>
                <?php endif; ?>
                <?php foreach ($solicitacoes as $solicitacao): ?>
                    <?php $whatsapp = linkWhatsapp($solicitacao['telefone'], $solicitacao['nome'], $solicitacao['interesse']); ?>
                    <tr>
                        <td class="text-nowrap"><?= e(date('d/m/Y H:i', strtotime($solicitacao['data_contato']))) ?></td>
                        <td>
                            <div class="fw-semibold"><?= e($solicitacao['nome']) ?></div>
                            <small class="text-white-50 d-block"><?= e($solicitacao['email']) ?></small>
                            <?php if (!empty($solicitacao['telefone'])): ?>
                                <small class="text-white-50"><?= e($solicitacao['telefone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($solicitacao['produto'] ?: 'Não vinculado') ?></td>
                        <td><?= e($solicitacao['interesse'] ?: 'Não informado') ?></td>
                        <td style="min-width: 260px; white-space: normal;"><?= nl2br(e($solicitacao['mensagem'] ?? '')) ?></td>
                        <td class="text-end text-nowrap">
                            <?php if ($whatsapp): ?>
                                <a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success">
                                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                </a>
                            <?php else: ?>
                                <span class="small text-white-50">Sem telefone válido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
