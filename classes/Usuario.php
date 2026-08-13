<?php

class Usuario
{
    public static function validarNome(string $nome): bool
    {
        return mb_strlen(trim($nome)) >= 3 && mb_strlen(trim($nome)) <= 120;
    }

    public static function validarEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validarSenha(string $senha): bool
    {
        return strlen($senha) >= 8 && strlen($senha) <= 72;
    }

    public static function criarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
}
