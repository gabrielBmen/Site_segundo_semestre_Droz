<?php

class PedidoModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarItensDoCarrinho(array $idsProdutos): array
    {
        $idsProdutos = array_values(array_unique(array_filter(
            array_map('intval', $idsProdutos),
            fn (int $id): bool => $id > 0
        )));

        if ($idsProdutos === []) {
            return [];
        }

        $marcadores = implode(',', array_fill(0, count($idsProdutos), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT
                p.id_produto,
                p.nome,
                p.slug,
                p.preco,
                p.estoque,
                p.ativo,
                p.permite_pedido,
                c.nome AS categoria,
                (
                    SELECT i.caminho
                    FROM imagens_produto i
                    WHERE i.id_produto = p.id_produto
                    ORDER BY i.principal DESC, i.id_imagem ASC
                    LIMIT 1
                ) AS imagem
             FROM produtos p
             INNER JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.id_produto IN ($marcadores)
             ORDER BY p.nome ASC"
        );
        $stmt->execute($idsProdutos);

        return $stmt->fetchAll();
    }

    public function produtoDisponivelParaPedido(int $idProduto): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_produto, nome, estoque
             FROM produtos
             WHERE id_produto = :id
               AND ativo = TRUE
               AND permite_pedido = TRUE
             LIMIT 1"
        );
        $stmt->execute([':id' => $idProduto]);

        $produto = $stmt->fetch();
        return $produto ?: null;
    }

    public function listarClientesParaVendaManual(): array
    {
        $stmt = $this->pdo->query(
            "SELECT c.id_cliente, c.nome, c.email
             FROM clientes c
             INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
             WHERE u.ativo = TRUE
             ORDER BY c.nome ASC, c.id_cliente ASC"
        );

        return array_map(static fn (array $cliente): array => [
            'id_cliente' => (int) $cliente['id_cliente'],
            'nome' => (string) $cliente['nome'],
            'email' => (string) $cliente['email'],
        ], $stmt->fetchAll());
    }

    /**
     * Registra uma venda fechada pelo administrador sem alterar o preço público
     * do produto. O valor negociado fica salvo somente no item do pedido.
     */
    public function criarVendaManual(
        int $idCliente,
        int $idProduto,
        int $quantidade,
        float $precoUnitario
    ): int {
        if ($idCliente <= 0 || $idProduto <= 0 || $quantidade <= 0 || $precoUnitario <= 0) {
            throw new InvalidArgumentException('Os dados da venda são inválidos.');
        }

        $precoUnitario = round($precoUnitario, 2);
        $valorTotal = round($precoUnitario * $quantidade, 2);

        if ($valorTotal > 99999999.99) {
            throw new InvalidArgumentException('O valor total da venda excede o limite permitido.');
        }

        $transacaoPropria = !$this->pdo->inTransaction();
        if ($transacaoPropria) {
            $this->pdo->beginTransaction();
        }

        try {
            $buscarCliente = $this->pdo->prepare(
                "SELECT c.id_cliente
                 FROM clientes c
                 INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                 WHERE c.id_cliente = :id_cliente AND u.ativo = TRUE
                 LIMIT 1"
            );
            $buscarCliente->execute([':id_cliente' => $idCliente]);

            if (!$buscarCliente->fetchColumn()) {
                throw new RuntimeException('Selecione um cliente ativo para registrar a venda.');
            }

            $buscarProduto = $this->pdo->prepare(
                "SELECT id_produto, nome, estoque
                 FROM produtos
                 WHERE id_produto = :id_produto AND ativo = TRUE
                 LIMIT 1
                 FOR UPDATE"
            );
            $buscarProduto->execute([':id_produto' => $idProduto]);
            $produto = $buscarProduto->fetch();

            if (!$produto) {
                throw new RuntimeException('O produto selecionado não está ativo.');
            }

            if ($quantidade > (int) $produto['estoque']) {
                throw new RuntimeException(
                    'Estoque insuficiente para “' . $produto['nome'] . '”. Disponível: ' . (int) $produto['estoque'] . '.'
                );
            }

            $stmtPedido = $this->pdo->prepare(
                "INSERT INTO pedidos (id_cliente, valor_total, status)
                 VALUES (:id_cliente, :valor_total, 'concluido')"
            );
            $stmtPedido->execute([
                ':id_cliente' => $idCliente,
                ':valor_total' => $valorTotal,
            ]);
            $idPedido = (int) $this->pdo->lastInsertId();

            $stmtItem = $this->pdo->prepare(
                "INSERT INTO pedido_produto (id_pedido, id_produto, quantidade, preco_unitario)
                 VALUES (:id_pedido, :id_produto, :quantidade, :preco_unitario)"
            );
            $stmtItem->execute([
                ':id_pedido' => $idPedido,
                ':id_produto' => $idProduto,
                ':quantidade' => $quantidade,
                ':preco_unitario' => $precoUnitario,
            ]);

            $baixarEstoque = $this->pdo->prepare(
                "UPDATE produtos
                 SET estoque = estoque - :quantidade_baixa
                 WHERE id_produto = :id_produto AND estoque >= :quantidade_minima"
            );
            $baixarEstoque->execute([
                ':quantidade_baixa' => $quantidade,
                ':quantidade_minima' => $quantidade,
                ':id_produto' => $idProduto,
            ]);

            if ($baixarEstoque->rowCount() !== 1) {
                throw new RuntimeException('O estoque mudou durante o registro. Atualize a página e tente novamente.');
            }

            if ($transacaoPropria) {
                $this->pdo->commit();
            }

            return $idPedido;
        } catch (Throwable $e) {
            if ($transacaoPropria && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Cria um pedido pendente, registra os itens e baixa o estoque na mesma transação.
     * O preço usado é sempre o que está salvo no banco no momento da finalização.
     */
    public function criarPedido(int $idCliente, array $itens): int
    {
        if ($idCliente <= 0 || $itens === []) {
            throw new InvalidArgumentException('Não há itens válidos para finalizar o pedido.');
        }

        $this->pdo->beginTransaction();

        try {
            $itensValidados = [];
            $valorTotal = 0.0;
            $buscarProduto = $this->pdo->prepare(
                "SELECT id_produto, nome, preco, estoque, ativo, permite_pedido
                 FROM produtos
                 WHERE id_produto = :id
                 FOR UPDATE"
            );

            foreach ($itens as $idProduto => $quantidade) {
                $idProduto = (int) $idProduto;
                $quantidade = (int) $quantidade;

                if ($idProduto <= 0 || $quantidade <= 0) {
                    throw new InvalidArgumentException('Há uma quantidade inválida no pedido.');
                }

                $buscarProduto->execute([':id' => $idProduto]);
                $produto = $buscarProduto->fetch();

                if (!$produto || !(bool) $produto['ativo'] || !(bool) $produto['permite_pedido']) {
                    throw new RuntimeException('Um item do pedido não está mais disponível para compra online.');
                }

                if ($produto['preco'] === null) {
                    throw new RuntimeException('Um item do pedido não possui preço disponível para compra online.');
                }

                if ($quantidade > (int) $produto['estoque']) {
                    throw new RuntimeException('Estoque insuficiente para “' . $produto['nome'] . '”.');
                }

                $preco = (float) $produto['preco'];
                $valorTotal += $preco * $quantidade;
                $itensValidados[] = [
                    'id_produto' => $idProduto,
                    'quantidade' => $quantidade,
                    'preco' => $preco,
                ];
            }

            $stmtPedido = $this->pdo->prepare(
                "INSERT INTO pedidos (id_cliente, valor_total, status)
                 VALUES (:id_cliente, :valor_total, 'pendente')"
            );
            $stmtPedido->execute([
                ':id_cliente' => $idCliente,
                ':valor_total' => $valorTotal,
            ]);
            $idPedido = (int) $this->pdo->lastInsertId();

            $stmtItem = $this->pdo->prepare(
                "INSERT INTO pedido_produto (id_pedido, id_produto, quantidade, preco_unitario)
                 VALUES (:id_pedido, :id_produto, :quantidade, :preco_unitario)"
            );
            $baixarEstoque = $this->pdo->prepare(
                "UPDATE produtos
                 SET estoque = estoque - :quantidade
                 WHERE id_produto = :id_produto"
            );

            foreach ($itensValidados as $item) {
                $stmtItem->execute([
                    ':id_pedido' => $idPedido,
                    ':id_produto' => $item['id_produto'],
                    ':quantidade' => $item['quantidade'],
                    ':preco_unitario' => $item['preco'],
                ]);
                $baixarEstoque->execute([
                    ':id_produto' => $item['id_produto'],
                    ':quantidade' => $item['quantidade'],
                ]);
            }

            $this->pdo->commit();
            return $idPedido;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function listarParaAdmin(): array
    {
        $stmt = $this->pdo->query(
            "SELECT
                p.id_pedido,
                p.data_pedido,
                p.valor_total,
                p.status,
                c.nome AS cliente,
                c.email,
                c.telefone,
                pp.quantidade,
                pp.preco_unitario,
                pr.nome AS produto
             FROM pedidos p
             INNER JOIN clientes c ON c.id_cliente = p.id_cliente
             LEFT JOIN pedido_produto pp ON pp.id_pedido = p.id_pedido
             LEFT JOIN produtos pr ON pr.id_produto = pp.id_produto
             ORDER BY p.data_pedido DESC, p.id_pedido DESC, pr.nome ASC"
        );

        $pedidos = [];
        foreach ($stmt->fetchAll() as $linha) {
            $idPedido = (int) $linha['id_pedido'];

            if (!isset($pedidos[$idPedido])) {
                $pedidos[$idPedido] = [
                    'id_pedido' => $idPedido,
                    'data_pedido' => $linha['data_pedido'],
                    'valor_total' => $linha['valor_total'],
                    'status' => $linha['status'],
                    'cliente' => $linha['cliente'],
                    'email' => $linha['email'],
                    'telefone' => $linha['telefone'],
                    'itens' => [],
                ];
            }

            if ($linha['produto'] !== null) {
                $pedidos[$idPedido]['itens'][] = [
                    'produto' => $linha['produto'],
                    'quantidade' => (int) $linha['quantidade'],
                    'preco_unitario' => $linha['preco_unitario'],
                ];
            }
        }

        return array_values($pedidos);
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                p.id_pedido,
                p.data_pedido,
                p.valor_total,
                p.status,
                pp.quantidade,
                pp.preco_unitario,
                pr.id_produto,
                pr.nome AS produto,
                pr.slug
             FROM pedidos p
             INNER JOIN clientes c ON c.id_cliente = p.id_cliente
             LEFT JOIN pedido_produto pp ON pp.id_pedido = p.id_pedido
             LEFT JOIN produtos pr ON pr.id_produto = pp.id_produto
             WHERE c.id_usuario = :id_usuario
             ORDER BY p.data_pedido DESC, p.id_pedido DESC, pr.nome ASC"
        );
        $stmt->execute([':id_usuario' => $idUsuario]);

        $pedidos = [];
        foreach ($stmt->fetchAll() as $linha) {
            $idPedido = (int) $linha['id_pedido'];
            if (!isset($pedidos[$idPedido])) {
                $pedidos[$idPedido] = [
                    'id_pedido' => $idPedido,
                    'data_pedido' => (string) $linha['data_pedido'],
                    'valor_total' => (float) $linha['valor_total'],
                    'status' => (string) $linha['status'],
                    'itens' => [],
                ];
            }

            if ($linha['produto'] !== null) {
                $quantidade = (int) $linha['quantidade'];
                $precoUnitario = (float) $linha['preco_unitario'];
                $pedidos[$idPedido]['itens'][] = [
                    'id_produto' => (int) $linha['id_produto'],
                    'produto' => (string) $linha['produto'],
                    'slug' => (string) $linha['slug'],
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoUnitario,
                    'subtotal' => $quantidade * $precoUnitario,
                ];
            }
        }

        return array_values($pedidos);
    }

    public function atualizarStatus(int $idPedido, string $status): bool
    {
        $statusPermitidos = ['pendente', 'aprovado', 'cancelado', 'concluido'];
        if ($idPedido <= 0 || !in_array($status, $statusPermitidos, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE pedidos SET status = :status WHERE id_pedido = :id_pedido"
        );

        return $stmt->execute([
            ':status' => $status,
            ':id_pedido' => $idPedido,
        ]);
    }
}
