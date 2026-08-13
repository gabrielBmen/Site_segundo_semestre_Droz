<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class Login
{
    public function __construct(private UsuarioModel $usuarioModel)
    {
    }

    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if (!$usuario) {
            return null;
        }

        if (!(bool) $usuario['ativo']) {
            return null;
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return null;
        }

        unset($usuario['senha']);

        return $usuario;
    }
}
