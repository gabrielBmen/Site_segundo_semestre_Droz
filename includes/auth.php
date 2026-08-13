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
    if (!usuarioLogado() || ($_SESSION['usuario']['tipo'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Acesso negado.');
    }
}
