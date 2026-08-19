<?php

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../classes/Login.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/LoginController.php';

$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    $dados = $_POST;
}

$email = (string) ($dados['email'] ?? '');
$senha = (string) ($dados['senha'] ?? '');

$redirect = (string) ($dados['redirect'] ?? '');

if ($redirect === '' || str_starts_with($redirect, '//') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $redirect)) {
    $redirect = 'index.php';
}

try {
    $usuarioModel = new UsuarioModel($pdo);
    $login = new Login($usuarioModel);
    $controller = new LoginController($login);

    $resultado = $controller->entrar($email, $senha);

    http_response_code($resultado['status']);

    unset($resultado['status']);
    $resultado['redirect'] = $redirect;

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno do servidor.',
    ], JSON_UNESCAPED_UNICODE);
}
