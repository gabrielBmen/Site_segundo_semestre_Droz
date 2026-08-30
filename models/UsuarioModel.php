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

    public function buscarPerfilPorId(int $idUsuario): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                u.id_usuario,
                u.nome,
                u.email,
                u.tipo,
                u.ativo,
                u.foto_perfil,
                u.data_criacao,
                c.id_cliente,
                c.telefone,
                COUNT(DISTINCT p.id_pedido) AS total_pedidos,
                COALESCE(SUM(CASE WHEN p.status <> 'cancelado' THEN p.valor_total ELSE 0 END), 0) AS valor_pedidos
             FROM usuarios u
             LEFT JOIN clientes c ON c.id_usuario = u.id_usuario
             LEFT JOIN pedidos p ON p.id_cliente = c.id_cliente
             WHERE u.id_usuario = :id_usuario
             GROUP BY u.id_usuario, u.nome, u.email, u.tipo, u.ativo, u.foto_perfil,
                      u.data_criacao, c.id_cliente, c.telefone
             LIMIT 1"
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        $perfil = $stmt->fetch();

        return $perfil ?: null;
    }

    public function atualizarTelefoneDoCliente(int $idUsuario, ?string $telefone): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clientes SET telefone = :telefone WHERE id_usuario = :id_usuario'
        );
        $stmt->execute([
            ':telefone' => $telefone,
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->rowCount() > 0 || $this->buscarClientePorUsuario($idUsuario) !== null;
    }

    public function atualizarFotoPerfil(int $idUsuario, ?string $caminho): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET foto_perfil = :foto_perfil WHERE id_usuario = :id_usuario'
        );
        $stmt->execute([
            ':foto_perfil' => $caminho,
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->rowCount() > 0 || $this->buscarPorId($idUsuario) !== null;
    }

    public function listarParaAdmin(): array
    {
        $sql = "
            SELECT
                u.id_usuario,
                u.nome,
                u.email,
                u.tipo,
                u.ativo,
                u.data_criacao,
                c.telefone,
                COUNT(p.id_pedido) AS total_pedidos
            FROM usuarios u
            LEFT JOIN clientes c ON c.id_usuario = u.id_usuario
            LEFT JOIN pedidos p ON p.id_cliente = c.id_cliente
            GROUP BY u.id_usuario, u.nome, u.email, u.tipo, u.ativo, u.data_criacao, c.telefone
            ORDER BY u.id_usuario ASC
        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarCompletoPorId(int $idUsuario): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_usuario, u.nome, u.email, u.tipo, u.ativo, c.telefone
             FROM usuarios u
             LEFT JOIN clientes c ON c.id_usuario = u.id_usuario
             WHERE u.id_usuario = :id_usuario
             LIMIT 1'
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function contarAdminsAtivos(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin' AND ativo = TRUE"
        )->fetchColumn();
    }

    public function possuiPedidos(int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM clientes c
             INNER JOIN pedidos p ON p.id_cliente = c.id_cliente
             WHERE c.id_usuario = :id_usuario'
        );
        $stmt->execute([':id_usuario' => $idUsuario]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function criarGerenciado(
        string $nome,
        string $email,
        string $senha,
        string $tipo,
        bool $ativo,
        string $telefone
    ): int {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha, tipo, ativo)
                 VALUES (:nome, :email, :senha, :tipo, :ativo)'
            );
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                ':tipo' => $tipo,
                ':ativo' => $ativo ? 1 : 0,
            ]);
            $idUsuario = (int) $this->pdo->lastInsertId();

            if ($tipo === 'cliente') {
                $stmtCliente = $this->pdo->prepare(
                    'INSERT INTO clientes (id_usuario, nome, email, telefone)
                     VALUES (:id_usuario, :nome, :email, :telefone)'
                );
                $stmtCliente->execute([
                    ':id_usuario' => $idUsuario,
                    ':nome' => $nome,
                    ':email' => $email,
                    ':telefone' => $telefone !== '' ? $telefone : null,
                ]);
            }

            $this->pdo->commit();
            return $idUsuario;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function atualizarGerenciado(
        int $idUsuario,
        string $nome,
        string $email,
        ?string $senha,
        string $tipo,
        bool $ativo,
        string $telefone
    ): void {
        try {
            $this->pdo->beginTransaction();
            $sqlSenha = $senha !== null ? ', senha = :senha' : '';
            $stmt = $this->pdo->prepare(
                "UPDATE usuarios
                 SET nome = :nome, email = :email, tipo = :tipo, ativo = :ativo{$sqlSenha}
                 WHERE id_usuario = :id_usuario"
            );
            $params = [
                ':nome' => $nome,
                ':email' => $email,
                ':tipo' => $tipo,
                ':ativo' => $ativo ? 1 : 0,
                ':id_usuario' => $idUsuario,
            ];
            if ($senha !== null) $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT);
            $stmt->execute($params);

            $cliente = $this->buscarClientePorUsuario($idUsuario);
            if ($tipo === 'cliente' && $cliente) {
                $stmtCliente = $this->pdo->prepare(
                    'UPDATE clientes SET nome = :nome, email = :email, telefone = :telefone
                     WHERE id_usuario = :id_usuario'
                );
                $stmtCliente->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':telefone' => $telefone !== '' ? $telefone : null,
                    ':id_usuario' => $idUsuario,
                ]);
            } elseif ($tipo === 'cliente') {
                $stmtCliente = $this->pdo->prepare(
                    'INSERT INTO clientes (id_usuario, nome, email, telefone)
                     VALUES (:id_usuario, :nome, :email, :telefone)'
                );
                $stmtCliente->execute([
                    ':id_usuario' => $idUsuario,
                    ':nome' => $nome,
                    ':email' => $email,
                    ':telefone' => $telefone !== '' ? $telefone : null,
                ]);
            } elseif ($cliente) {
                $stmtCliente = $this->pdo->prepare('DELETE FROM clientes WHERE id_usuario = :id_usuario');
                $stmtCliente->execute([':id_usuario' => $idUsuario]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function excluir(int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id_usuario = :id_usuario');
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }
}
