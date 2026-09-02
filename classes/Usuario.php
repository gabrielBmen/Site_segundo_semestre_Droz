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

    public static function somenteDigitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    public static function validarCep(string $cep): bool
    {
        $cep = self::somenteDigitos($cep);
        return strlen($cep) === 8;
    }

    public static function validarCpf(string $cpf): bool
    {
        $cpf = self::somenteDigitos($cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }
        $primeiroDigito = ($soma % 11) < 2 ? 0 : 11 - ($soma % 11);

        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }
        $segundoDigito = ($soma % 11) < 2 ? 0 : 11 - ($soma % 11);

        return $primeiroDigito === (int) $cpf[9]
            && $segundoDigito === (int) $cpf[10];
    }

    public static function validarCnpj(string $cnpj): bool
    {
        $cnpj = self::somenteDigitos($cnpj);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $calcularDigito = static function (string $numero, array $pesos): int {
            $soma = 0;
            foreach ($pesos as $indice => $peso) {
                $soma += (int) $numero[$indice] * $peso;
            }

            $resto = $soma % 11;
            return $resto < 2 ? 0 : 11 - $resto;
        };

        $primeiroDigito = $calcularDigito($cnpj, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $segundoDigito = $calcularDigito($cnpj, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $primeiroDigito === (int) $cnpj[12]
            && $segundoDigito === (int) $cnpj[13];
    }

    public static function criarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
}
