<?php

class UsuarioModel
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "
            SELECT id_usuario, nome, email, senha, tipo, ativo
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function buscarPorId(int $idUsuario): ?array
    {
        $sql = "
            SELECT id_usuario, nome, email, tipo, ativo
            FROM usuarios
            WHERE id_usuario = :id_usuario
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function criarCliente(
        string $nome,
        string $email,
        string $senha,
        string $telefone = ''
    ): int {
        try {
            $this->pdo->beginTransaction();

            $sqlUsuario = "
                INSERT INTO usuarios (nome, email, senha, tipo, ativo)
                VALUES (:nome, :email, :senha, 'cliente', TRUE)
            ";

            $stmtUsuario = $this->pdo->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]);

            $idUsuario = (int) $this->pdo->lastInsertId();

            $sqlCliente = "
                INSERT INTO clientes (id_usuario, nome, email, telefone)
                VALUES (:id_usuario, :nome, :email, :telefone)
            ";

            $stmtCliente = $this->pdo->prepare($sqlCliente);
            $stmtCliente->execute([
                ':id_usuario' => $idUsuario,
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone !== '' ? $telefone : null,
            ]);

            $this->pdo->commit();

            return $idUsuario;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function buscarClientePorUsuario(int $idUsuario): ?array
    {
        $sql = "
            SELECT id_cliente, id_usuario, nome, email, telefone
            FROM clientes
            WHERE id_usuario = :id_usuario
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);

        $cliente = $stmt->fetch();

        return $cliente ?: null;
    }
}
