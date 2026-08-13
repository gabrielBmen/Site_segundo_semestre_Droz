<?php

require_once __DIR__ . '/../classes/Login.php';

class LoginController
{
    public function __construct(private Login $login)
    {
    }

    public function entrar(string $email, string $senha): array
    {
        $email = mb_strtolower(trim($email));

        if ($email === '' || $senha === '') {
            return [
                'sucesso' => false,
                'mensagem' => 'Informe e-mail e senha.',
                'status' => 422,
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Informe um e-mail válido.',
                'status' => 422,
            ];
        }

        $usuario = $this->login->autenticar($email, $senha);

        if (!$usuario) {
            return [
                'sucesso' => false,
                'mensagem' => 'E-mail ou senha inválidos.',
                'status' => 401,
            ];
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo' => $usuario['tipo'],
        ];

        return [
            'sucesso' => true,
            'mensagem' => 'Login realizado com sucesso.',
            'usuario' => $_SESSION['usuario'],
            'status' => 200,
        ];
    }
}
