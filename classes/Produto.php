<?php

class Produto
{
    public function __construct(
        public readonly ?int $idProduto,
        public readonly int $idCategoria,
        public readonly string $nome,
        public readonly string $descricao,
        public readonly float $preco,
        public readonly int $estoque,
        public readonly bool $permitePedido,
        public readonly bool $ativo,
        public readonly ?string $slug = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id_produto' => $this->idProduto,
            'id_categoria' => $this->idCategoria,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco' => $this->preco,
            'estoque' => $this->estoque,
            'permite_pedido' => $this->permitePedido,
            'ativo' => $this->ativo,
            'slug' => $this->slug,
        ];
    }
}
