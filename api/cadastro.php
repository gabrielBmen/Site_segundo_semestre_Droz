<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    $dados = $_POST;
}

try {
    $usuarioModel = new UsuarioModel($pdo);
    $controller = new UsuarioController($usuarioModel);

    $resultado = $controller->cadastrar(
        (string) ($dados['nome'] ?? ''),
        (string) ($dados['email'] ?? ''),
        (string) ($dados['senha'] ?? ''),
        (string) ($dados['confirmar_senha'] ?? ''),
        (string) ($dados['telefone'] ?? '')
    );

    http_response_code($resultado['status']);
    unset($resultado['status']);

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno do servidor.',
    ], JSON_UNESCAPED_UNICODE);
}
