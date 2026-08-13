<?php

require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController
{
    public function __construct(private UsuarioModel $usuarioModel)
    {
    }

    public function cadastrar(
        string $nome,
        string $email,
        string $senha,
        string $confirmarSenha,
        string $telefone = ''
    ): array {
        $nome = trim($nome);
        $email = mb_strtolower(trim($email));
        $telefone = trim($telefone);

        if (!Usuario::validarNome($nome)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Informe um nome entre 3 e 120 caracteres.',
                'status' => 422,
            ];
        }

        if (!Usuario::validarEmail($email)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Informe um e-mail válido.',
                'status' => 422,
            ];
        }

        if (!Usuario::validarSenha($senha)) {
            return [
                'sucesso' => false,
                'mensagem' => 'A senha deve ter entre 8 e 72 caracteres.',
                'status' => 422,
            ];
        }

        if ($senha !== $confirmarSenha) {
            return [
                'sucesso' => false,
                'mensagem' => 'As senhas não conferem.',
                'status' => 422,
            ];
        }

        if ($this->usuarioModel->buscarPorEmail($email)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Este e-mail já está cadastrado.',
                'status' => 409,
            ];
        }

        try {
            $this->usuarioModel->criarCliente(
                $nome,
                $email,
                $senha,
                $telefone
            );

            return [
                'sucesso' => true,
                'mensagem' => 'Cadastro realizado com sucesso.',
                'status' => 201,
            ];
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Este e-mail já está cadastrado.',
                    'status' => 409,
                ];
            }

            throw $e;
        }
    }
}
