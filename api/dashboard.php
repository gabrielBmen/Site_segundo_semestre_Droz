<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
exigirAdminJson();
require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Dados brutos consolidados pela View.
    $stmtItens = $pdo->query(<<<SQL
        SELECT
            id_pedido,
            data_pedido,
            status,
            id_cliente,
            cliente,
            id_produto,
            produto,
            categoria,
            quantidade,
            preco_unitario,
            valor_item
        FROM vw_pedido_itens_analiticos
        ORDER BY data_pedido DESC, id_pedido DESC, id_produto ASC
    SQL);

    $itens = $stmtItens->fetchAll();

    // CTE usada para consolidar pedidos por status.
    $stmtStatus = $pdo->query(<<<SQL
        WITH pedidos_consolidados AS (
            SELECT
                id_pedido,
                status,
                SUM(valor_item) AS total_pedido
            FROM vw_pedido_itens_analiticos
            GROUP BY id_pedido, status
        )
        SELECT
            status,
            COUNT(*) AS quantidade_pedidos,
            COALESCE(SUM(total_pedido), 0) AS valor_total
        FROM pedidos_consolidados
        GROUP BY status
        ORDER BY FIELD(status, 'pendente', 'aprovado', 'concluido', 'cancelado')
    SQL);

    $status = $stmtStatus->fetchAll();

    // Outra CTE para ranking dos produtos.
    $stmtProdutos = $pdo->query(<<<SQL
        WITH produto_totais AS (
            SELECT
                id_produto,
                produto,
                categoria,
                SUM(quantidade) AS quantidade_vendida,
                SUM(valor_item) AS faturamento
            FROM vw_pedido_itens_analiticos
            GROUP BY id_produto, produto, categoria
        )
        SELECT
            id_produto,
            produto,
            categoria,
            quantidade_vendida,
            faturamento
        FROM produto_totais
        ORDER BY quantidade_vendida DESC, faturamento DESC
        LIMIT 5
    SQL);

    $topProdutos = $stmtProdutos->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'dados' => [
            'itens' => $itens,
            'status' => $status,
            'top_produtos' => $topProdutos,
            'vazio' => count($itens) === 0,
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível carregar os dados da dashboard.',
    ], JSON_UNESCAPED_UNICODE);
}
