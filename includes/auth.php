<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function usuarioLogado(): bool
{
    return isset($_SESSION['usuario']['id_usuario']);
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
