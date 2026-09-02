<?php

require_once __DIR__ . '/auth.php';

function podeVerPreco(array $produto): bool
{
    return usuarioLogado() && (bool) ($produto['permite_pedido'] ?? false);
}

function podeVerDetalhes(): bool
{
    return usuarioLogado();
}

function podeEnviarContato(): bool
{
    return usuarioLogado();
}
