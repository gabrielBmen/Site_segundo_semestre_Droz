<?php

require_once __DIR__ . '/../models/CategoriaModel.php';

class CategoriaController
{
    public function __construct(private CategoriaModel $categoriaModel)
    {
    }

    public function listar(): array
    {
        return $this->categoriaModel->listarComTotalProdutos();
    }

    public function buscar(int $idCategoria): ?array
    {
        return $this->categoriaModel->buscarPorId($idCategoria);
    }

    public function salvar(int $idCategoria, string $nome, bool $ativo): array
    {
        $nome = trim($nome);
        if (mb_strlen($nome) < 2 || mb_strlen($nome) > 80) {
            return ['sucesso' => false, 'mensagem' => 'Informe um nome de categoria entre 2 e 80 caracteres.'];
        }

        try {
            if ($idCategoria > 0) {
                if (!$this->categoriaModel->atualizar($idCategoria, $nome, $ativo)) {
                    return ['sucesso' => false, 'mensagem' => 'Categoria não encontrada.'];
                }
                return ['sucesso' => true, 'mensagem' => 'Categoria atualizada com sucesso.'];
            }

            $this->categoriaModel->criar($nome, $ativo);
            return ['sucesso' => true, 'mensagem' => 'Categoria criada com sucesso.'];
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return ['sucesso' => false, 'mensagem' => 'Já existe uma categoria com esse nome.'];
            }
            throw $e;
        }
    }

    public function alternarStatus(int $idCategoria): array
    {
        if ($idCategoria <= 0 || !$this->categoriaModel->alternarStatus($idCategoria)) {
            return ['sucesso' => false, 'mensagem' => 'Categoria não encontrada.'];
        }
        return ['sucesso' => true, 'mensagem' => 'Status da categoria atualizado.'];
    }

    public function excluir(int $idCategoria): array
    {
        if ($idCategoria <= 0 || !$this->categoriaModel->buscarPorId($idCategoria)) {
            return ['sucesso' => false, 'mensagem' => 'Categoria não encontrada.'];
        }
        if ($this->categoriaModel->contarProdutos($idCategoria) > 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Não é possível excluir uma categoria que possui produtos. Reassocie ou exclua os produtos antes.',
            ];
        }

        $this->categoriaModel->excluir($idCategoria);
        return ['sucesso' => true, 'mensagem' => 'Categoria excluída com sucesso.'];
    }
}
