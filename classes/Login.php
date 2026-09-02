<?php

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/Usuario.php';

class Login
{
    public function __construct(private UsuarioModel $usuarioModel)
    {
    }

    public function autenticar(string $email, string $senha): array
    {
        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if (!$usuario) {
            return ['usuario' => null];
        }

        if (!(bool) $usuario['ativo']) {
            return ['usuario' => null];
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return ['usuario' => null];
        }

        if ($usuario['tipo'] === 'cliente'
            && (!Usuario::validarCpf((string) ($usuario['cpf'] ?? ''))
                || !Usuario::validarCnpj((string) ($usuario['cnpj'] ?? '')))) {
            return [
                'usuario' => null,
                'mensagem' => 'Para acessar, complete o cadastro da empresa com CPF e CNPJ válidos.',
            ];
        }

        unset($usuario['senha']);

        return ['usuario' => $usuario];
    }
}
