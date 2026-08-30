<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
exigirAdminJson();
require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

/** @param array<string, mixed> $item */
function normalizarItemDashboard(array $item): array
{
    return [
        'id_pedido' => (int) $item['id_pedido'],
        'data_pedido' => (string) $item['data_pedido'],
        'status' => (string) $item['status'],
        'id_cliente' => (int) $item['id_cliente'],
        'cliente' => (string) $item['cliente'],
        'id_produto' => (int) $item['id_produto'],
        'produto' => (string) $item['produto'],
        'categoria' => (string) $item['categoria'],
        'quantidade' => (int) $item['quantidade'],
        'preco_unitario' => (float) $item['preco_unitario'],
        'valor_item' => (float) $item['valor_item'],
    ];
}

try {
    $periodo = (string) ($_GET['periodo'] ?? 'mes');
    $hoje = new DateTimeImmutable('today');
    $inicio = $hoje->modify('-29 days');
    $fim = $hoje;

    if ($periodo === 'semana') {
        $inicio = $hoje->modify('-6 days');
    } elseif ($periodo === 'personalizado') {
        $inicioInformado = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($_GET['inicio'] ?? ''));
        $fimInformado = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($_GET['fim'] ?? ''));

        if (!$inicioInformado || !$fimInformado || $inicioInformado > $fimInformado) {
            throw new InvalidArgumentException('Informe um intervalo de datas válido.');
        }

        $inicio = $inicioInformado;
        $fim = $fimInformado;
    } else {
        $periodo = 'mes';
    }

    $busca = substr(trim((string) ($_GET['busca'] ?? '')), 0, 120);
    $status = (string) ($_GET['status'] ?? '');
    if (!in_array($status, ['', 'pendente', 'aprovado', 'concluido', 'cancelado'], true)) {
        throw new InvalidArgumentException('O status selecionado é inválido.');
    }

    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $porPaginaInformado = (int) ($_GET['por_pagina'] ?? 10);
    $porPagina = in_array($porPaginaInformado, [5, 10, 20, 50], true) ? $porPaginaInformado : 10;
    $offset = ($pagina - 1) * $porPagina;

    // Filtros, consolidação e paginação ficam centralizados na Stored Procedure.
    $stmt = $pdo->prepare('CALL sp_dashboard_indicadores(?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $inicio->format('Y-m-d 00:00:00'),
        $fim->modify('+1 day')->format('Y-m-d 00:00:00'),
        $busca,
        $status,
        $porPagina,
        $offset,
    ]);

    $itens = array_map('normalizarItemDashboard', $stmt->fetchAll());

    $stmt->nextRowset();
    $statusResumo = array_map(static function (array $item): array {
        return [
            'status' => (string) $item['status'],
            'quantidade_pedidos' => (int) $item['quantidade_pedidos'],
            'valor_total' => (float) $item['valor_total'],
        ];
    }, $stmt->fetchAll());

    $stmt->nextRowset();
    $topProdutos = array_map(static function (array $item): array {
        return [
            'id_produto' => (int) $item['id_produto'],
            'produto' => (string) $item['produto'],
            'categoria' => (string) $item['categoria'],
            'quantidade_vendida' => (int) $item['quantidade_vendida'],
            'faturamento' => (float) $item['faturamento'],
        ];
    }, $stmt->fetchAll());

    $stmt->nextRowset();
    $itensPaginados = array_map('normalizarItemDashboard', $stmt->fetchAll());

    $stmt->nextRowset();
    $linhaTotal = $stmt->fetch();
    $total = (int) ($linhaTotal['total'] ?? 0);
    $stmt->closeCursor();

    $totalPaginas = max(1, (int) ceil($total / $porPagina));

    echo json_encode([
        'sucesso' => true,
        'dados' => [
            'itens' => $itens,
            'status' => $statusResumo,
            'top_produtos' => $topProdutos,
            'itens_paginados' => $itensPaginados,
            'vazio' => count($itens) === 0,
            'periodo' => [
                'tipo' => $periodo,
                'inicio' => $inicio->format('Y-m-d'),
                'fim' => $fim->format('Y-m-d'),
            ],
            'filtros' => ['busca' => $busca, 'status' => $status],
            'paginacao' => [
                'pagina' => min($pagina, $totalPaginas),
                'por_pagina' => $porPagina,
                'total' => $total,
                'total_paginas' => $totalPaginas,
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível carregar os dados da dashboard.',
    ], JSON_UNESCAPED_UNICODE);
}
