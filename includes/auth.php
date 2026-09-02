<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function usuarioLogado(): bool
{
    static $sessaoVerificada = false;
    static $usuarioAutenticado = false;

    if ($sessaoVerificada) {
        return $usuarioAutenticado;
    }

    $sessaoVerificada = true;
    $idUsuario = filter_var(
        $_SESSION['usuario']['id_usuario'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $emailSessao = mb_strtolower(trim((string) ($_SESSION['usuario']['email'] ?? '')));

    if ($idUsuario === false || !filter_var($emailSessao, FILTER_VALIDATE_EMAIL)) {
        unset($_SESSION['usuario']);
        return false;
    }

    try {
        global $pdo;

        if (!isset($pdo) || !($pdo instanceof PDO)) {
            require_once __DIR__ . '/../config/conexao.php';
        }

        $stmt = $pdo->prepare(
            'SELECT id_usuario, nome, email, tipo
             FROM usuarios
             WHERE id_usuario = :id_usuario
               AND email = :email
               AND ativo = TRUE
             LIMIT 1'
        );
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':email' => $emailSessao,
        ]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            unset($_SESSION['usuario'], $_SESSION['carrinho']);
            return false;
        }

        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo' => $usuario['tipo'],
        ];
        $usuarioAutenticado = true;

        return true;
    } catch (Throwable) {
        unset($_SESSION['usuario'], $_SESSION['carrinho']);
        return false;
    }
}

function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function usuarioAdmin(): bool
{
    return usuarioLogado() && ($_SESSION['usuario']['tipo'] ?? '') === 'admin';
}

function urlInternaValida(string $url): bool
{
    return $url !== ''
        && !str_starts_with($url, '//')
        && !preg_match('/^[a-z][a-z0-9+.-]*:/i', $url);
}

function exigirLogin(string $urlDepois = ''): void
{
    if (usuarioLogado()) {
        return;
    }

    $loginUrl = 'login.php';

    if (urlInternaValida($urlDepois)) {
        $loginUrl .= '?redirect=' . rawurlencode($urlDepois);
    }

    header('Location: ' . $loginUrl);
    exit;
}

function exigirAdmin(): void
{
    if (usuarioAdmin()) {
        return;
    }

    http_response_code(403);
    exit('Acesso negado. Área exclusiva para administradores.');
}

function exigirAdminJson(): void
{
    if (usuarioAdmin()) {
        return;
    }

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Acesso negado. Área exclusiva para administradores.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
