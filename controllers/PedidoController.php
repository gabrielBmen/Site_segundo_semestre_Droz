<?php

require_once __DIR__ . '/../models/PedidoModel.php';

class PedidoController
{
    public function __construct(private PedidoModel $pedidoModel)
    {
    }

    public function registrarVendaManual(array $dados): array
    {
        $idCliente = (int) ($dados['id_cliente'] ?? 0);
        $idProduto = (int) ($dados['id_produto'] ?? 0);
        $quantidade = filter_var(
            $dados['quantidade'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $precoUnitario = $this->normalizarDecimal($dados['preco_unitario'] ?? '');

        if ($idCliente <= 0) {
            return $this->erro('Selecione o cliente da venda.', 422);
        }

        if ($idProduto <= 0) {
            return $this->erro('Selecione o produto vendido.', 422);
        }

        if ($quantidade === false) {
            return $this->erro('Informe uma quantidade válida.', 422);
        }

        if ($precoUnitario === null || $precoUnitario <= 0) {
            return $this->erro('Informe um preço unitário negociado maior que zero.', 422);
        }

        try {
            $idPedido = $this->pedidoModel->criarVendaManual(
                $idCliente,
                $idProduto,
                $quantidade,
                $precoUnitario
            );

            return [
                'sucesso' => true,
                'mensagem' => "Venda registrada como pedido #{$idPedido} e estoque atualizado.",
                'id_pedido' => $idPedido,
                'status' => 201,
            ];
        } catch (InvalidArgumentException $e) {
            return $this->erro($e->getMessage(), 422);
        } catch (PDOException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            return $this->erro($e->getMessage(), 409);
        }
    }

    private function normalizarDecimal(mixed $valor): ?float
    {
        if (is_int($valor) || is_float($valor)) {
            return round((float) $valor, 2);
        }

        $texto = trim((string) $valor);
        $texto = str_replace('R$', '', $texto);
        $texto = preg_replace('/\s+/', '', $texto) ?? '';

        if (str_contains($texto, ',') && str_contains($texto, '.')) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } elseif (str_contains($texto, ',')) {
            $texto = str_replace(',', '.', $texto);
        }

        return $texto !== '' && is_numeric($texto) ? round((float) $texto, 2) : null;
    }

    private function erro(string $mensagem, int $status): array
    {
        return [
            'sucesso' => false,
            'mensagem' => $mensagem,
            'status' => $status,
        ];
    }
}
