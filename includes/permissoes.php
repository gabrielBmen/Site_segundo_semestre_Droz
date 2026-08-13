<?php

require_once __DIR__ . '/auth.php';

function podeVerPreco(): bool
{
    return usuarioLogado();
}

function podeVerDetalhes(): bool
{
    return usuarioLogado();
}

function podeEnviarContato(): bool
{
    return usuarioLogado();
}
