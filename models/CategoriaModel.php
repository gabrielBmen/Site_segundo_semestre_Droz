<?php

class CategoriaModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listarComTotalProdutos(): array
    {
        return $this->pdo->query(
            'SELECT c.id_categoria, c.nome, c.ativo, COUNT(p.id_produto) AS total_produtos
             FROM categorias c
             LEFT JOIN produtos p ON p.id_categoria = c.id_categoria
             GROUP BY c.id_categoria, c.nome, c.ativo
             ORDER BY c.id_categoria ASC'
        )->fetchAll();
    }

    public function buscarPorId(int $idCategoria): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_categoria, nome, ativo FROM categorias WHERE id_categoria = :id_categoria LIMIT 1'
        );
        $stmt->execute([':id_categoria' => $idCategoria]);
        $categoria = $stmt->fetch();
        return $categoria ?: null;
    }

    public function criar(string $nome, bool $ativo): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categorias (nome, ativo) VALUES (:nome, :ativo)');
        $stmt->execute([':nome' => $nome, ':ativo' => $ativo ? 1 : 0]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $idCategoria, string $nome, bool $ativo): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE categorias SET nome = :nome, ativo = :ativo WHERE id_categoria = :id_categoria'
        );
        $stmt->execute([
            ':nome' => $nome,
            ':ativo' => $ativo ? 1 : 0,
            ':id_categoria' => $idCategoria,
        ]);
        return $stmt->rowCount() > 0 || $this->buscarPorId($idCategoria) !== null;
    }

    public function alternarStatus(int $idCategoria): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE categorias SET ativo = NOT ativo WHERE id_categoria = :id_categoria'
        );
        $stmt->execute([':id_categoria' => $idCategoria]);
        return $stmt->rowCount() > 0;
    }

    public function contarProdutos(int $idCategoria): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM produtos WHERE id_categoria = :id_categoria');
        $stmt->execute([':id_categoria' => $idCategoria]);
        return (int) $stmt->fetchColumn();
    }

    public function excluir(int $idCategoria): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM categorias WHERE id_categoria = :id_categoria');
        $stmt->execute([':id_categoria' => $idCategoria]);
        return $stmt->rowCount() > 0;
    }
}
