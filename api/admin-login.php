<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Login.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/LoginController.php';

$dados = json_decode(file_get_contents('php://input'), true);
if (!is_array($dados)) {
    $dados = $_POST;
}

$email = (string) ($dados['email'] ?? '');
$senha = (string) ($dados['senha'] ?? '');

try {
    $usuarioModel = new UsuarioModel($pdo);
    $login = new Login($usuarioModel);
    $controller = new LoginController($login);

    $resultado = $controller->entrar($email, $senha);

    if (!$resultado['sucesso']) {
        http_response_code($resultado['status']);
        unset($resultado['status']);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (($resultado['usuario']['tipo'] ?? '') !== 'admin') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        http_response_code(403);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Esta conta não possui permissão de administrador.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login de administrador realizado com sucesso.',
        'usuario' => $resultado['usuario'],
        'redirect' => '/admin/',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno do servidor.',
    ], JSON_UNESCAPED_UNICODE);
}
