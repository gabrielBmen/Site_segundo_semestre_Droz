<?php

class ProdutoModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listar(?string $busca = null, ?int $idCategoria = null): array
    {
        $sql = "
            SELECT
                p.id_produto,
                p.id_categoria,
                p.nome,
                p.slug,
                p.descricao,
                p.preco,
                p.estoque,
                p.ativo,
                p.data_criacao,
                p.data_atualizacao,
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
            WHERE 1 = 1
        ";

        $params = [];

        if ($busca !== null && trim($busca) !== '') {
            $sql .= " AND (p.nome LIKE :busca OR p.slug LIKE :busca OR p.descricao LIKE :busca)";
            $params[':busca'] = '%' . trim($busca) . '%';
        }

        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " AND p.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        $sql .= " ORDER BY p.id_produto ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idProduto): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                p.id_produto,
                p.id_categoria,
                p.nome,
                p.slug,
                p.descricao,
                p.preco,
                p.estoque,
                p.ativo,
                p.data_criacao,
                p.data_atualizacao,
                c.nome AS categoria
             FROM produtos p
             INNER JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.id_produto = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $idProduto]);

        $produto = $stmt->fetch();
        if (!$produto) {
            return null;
        }

        $produto['imagens'] = $this->listarImagens($idProduto);

        return $produto;
    }

    public function listarCategorias(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id_categoria, nome, ativo
             FROM categorias
             WHERE ativo = TRUE
             ORDER BY nome ASC"
        );

        return $stmt->fetchAll();
    }

    public function buscarPorSlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id_produto, id_categoria, nome, slug, descricao, preco, estoque, ativo FROM produtos WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $produto = $stmt->fetch();
        return $produto ?: null;
    }

    public function slugExiste(string $slug, ?int $ignorarId = null): bool
    {
        $sql = "SELECT 1 FROM produtos WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($ignorarId !== null) {
            $sql .= " AND id_produto <> :id";
            $params[':id'] = $ignorarId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function criar(
        int $idCategoria,
        string $nome,
        string $slug,
        string $descricao,
        float $preco,
        int $estoque,
        bool $ativo
    ): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO produtos
                (id_categoria, nome, slug, descricao, preco, estoque, ativo)
             VALUES
                (:id_categoria, :nome, :slug, :descricao, :preco, :estoque, :ativo)"
        );

        $stmt->execute([
            ':id_categoria' => $idCategoria,
            ':nome' => $nome,
            ':slug' => $slug,
            ':descricao' => $descricao !== '' ? $descricao : null,
            ':preco' => $preco,
            ':estoque' => $estoque,
            ':ativo' => $ativo ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(
        int $idProduto,
        int $idCategoria,
        string $nome,
        string $descricao,
        float $preco,
        int $estoque,
        bool $ativo
    ): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE produtos
             SET id_categoria = :id_categoria,
                 nome = :nome,
                 descricao = :descricao,
                 preco = :preco,
                 estoque = :estoque,
                 ativo = :ativo
             WHERE id_produto = :id"
        );

        return $stmt->execute([
            ':id_categoria' => $idCategoria,
            ':nome' => $nome,
            ':descricao' => $descricao !== '' ? $descricao : null,
            ':preco' => $preco,
            ':estoque' => $estoque,
            ':ativo' => $ativo ? 1 : 0,
            ':id' => $idProduto,
        ]);
    }

    public function listarImagens(int $idProduto): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_imagem, id_produto, caminho, principal, data_criacao
             FROM imagens_produto
             WHERE id_produto = :id
             ORDER BY principal DESC, id_imagem ASC"
        );
        $stmt->execute([':id' => $idProduto]);

        return $stmt->fetchAll();
    }

    public function adicionarImagem(int $idProduto, string $caminho, bool $principal = false): int
    {
        if ($principal) {
            $this->pdo->prepare(
                "UPDATE imagens_produto SET principal = FALSE WHERE id_produto = :id"
            )->execute([':id' => $idProduto]);
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO imagens_produto (id_produto, caminho, principal)
             VALUES (:id_produto, :caminho, :principal)"
        );
        $stmt->execute([
            ':id_produto' => $idProduto,
            ':caminho' => $caminho,
            ':principal' => $principal ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function removerImagem(int $idImagem, int $idProduto): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_imagem, caminho, principal
             FROM imagens_produto
             WHERE id_imagem = :id_imagem AND id_produto = :id_produto
             LIMIT 1"
        );
        $stmt->execute([
            ':id_imagem' => $idImagem,
            ':id_produto' => $idProduto,
        ]);

        $imagem = $stmt->fetch();
        if (!$imagem) {
            return null;
        }

        $delete = $this->pdo->prepare(
            "DELETE FROM imagens_produto
             WHERE id_imagem = :id_imagem AND id_produto = :id_produto"
        );
        $delete->execute([
            ':id_imagem' => $idImagem,
            ':id_produto' => $idProduto,
        ]);

        return $imagem;
    }

    public function definirImagemPrincipal(int $idProduto, int $idImagem): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM imagens_produto
             WHERE id_produto = :id_produto AND id_imagem = :id_imagem
             LIMIT 1"
        );
        $stmt->execute([
            ':id_produto' => $idProduto,
            ':id_imagem' => $idImagem,
        ]);

        if (!$stmt->fetchColumn()) {
            return false;
        }

        $this->pdo->prepare(
            "UPDATE imagens_produto SET principal = FALSE WHERE id_produto = :id"
        )->execute([':id' => $idProduto]);

        $this->pdo->prepare(
            "UPDATE imagens_produto
             SET principal = TRUE
             WHERE id_produto = :id_produto AND id_imagem = :id_imagem"
        )->execute([
            ':id_produto' => $idProduto,
            ':id_imagem' => $idImagem,
        ]);

        return true;
    }

    public function imagemPrincipalExiste(int $idProduto): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM imagens_produto
             WHERE id_produto = :id AND principal = TRUE
             LIMIT 1"
        );
        $stmt->execute([':id' => $idProduto]);

        return (bool) $stmt->fetchColumn();
    }

    public function tornarPrimeiraImagemPrincipal(int $idProduto): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_imagem
             FROM imagens_produto
             WHERE id_produto = :id
             ORDER BY id_imagem ASC
             LIMIT 1"
        );
        $stmt->execute([':id' => $idProduto]);

        $idImagem = $stmt->fetchColumn();
        if (!$idImagem) {
            return false;
        }

        return $this->definirImagemPrincipal($idProduto, (int) $idImagem);
    }

    public function temPedidos(int $idProduto): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM pedido_produto WHERE id_produto = :id LIMIT 1"
        );
        $stmt->execute([':id' => $idProduto]);

        return (bool) $stmt->fetchColumn();
    }

    public function iniciarTransacao(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function confirmarTransacao(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function cancelarTransacao(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function excluir(int $idProduto): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE id_produto = :id");
        return $stmt->execute([':id' => $idProduto]);
    }
}
