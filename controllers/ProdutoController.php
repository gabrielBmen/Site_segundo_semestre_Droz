<?php

require_once __DIR__ . '/../classes/Produto.php';

class ProdutoController
{
    public function __construct(private ProdutoModel $produtoModel)
    {
    }

    public function listar(?string $busca = null, ?int $idCategoria = null): array
    {
        return [
            'sucesso' => true,
            'dados' => array_map(
                fn (array $produto): array => $this->normalizarProduto($produto),
                $this->produtoModel->listar($busca, $idCategoria)
            ),
        ];
    }

    public function buscar(int $idProduto): array
    {
        if ($idProduto <= 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto inválido.',
                'status' => 422,
            ];
        }

        $produto = $this->produtoModel->buscarPorId($idProduto);

        if (!$produto) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado.',
                'status' => 404,
            ];
        }

        return [
            'sucesso' => true,
            'dados' => $this->normalizarProduto($produto),
        ];
    }

    public function salvar(array $dados): array
    {
        $idProduto = isset($dados['id_produto']) && $dados['id_produto'] !== ''
            ? (int) $dados['id_produto']
            : null;

        $idCategoria = (int) ($dados['id_categoria'] ?? 0);
        $nome = trim((string) ($dados['nome'] ?? ''));
        $descricao = trim((string) ($dados['descricao'] ?? ''));
        $estoque = (int) ($dados['estoque'] ?? 0);
        $permitePedido = $this->normalizarBoolean($dados['permite_pedido'] ?? false);
        $preco = $permitePedido ? $this->normalizarDecimal($dados['preco'] ?? '') : null;
        $ativo = $this->normalizarBoolean($dados['ativo'] ?? true);

        if ($idCategoria <= 0) {
            return $this->erro('Selecione uma categoria válida.', 422);
        }

        if ($nome === '' || mb_strlen($nome) > 120) {
            return $this->erro('Informe um nome entre 1 e 120 caracteres.', 422);
        }

        if ($permitePedido && ($preco === null || $preco < 0)) {
            return $this->erro('Informe um preço válido para produtos disponíveis para pedido online.', 422);
        }

        if ($estoque < 0) {
            return $this->erro('O estoque não pode ser negativo.', 422);
        }

        if ($idProduto === null) {
            $slug = $this->gerarSlugUnico($nome);

            $novoId = $this->produtoModel->criar(
                $idCategoria,
                $nome,
                $slug,
                $descricao,
                $preco,
                $estoque,
                $permitePedido,
                $ativo
            );

            return [
                'sucesso' => true,
                'mensagem' => 'Produto criado com sucesso.',
                'id_produto' => $novoId,
            ];
        }

        $produtoAtual = $this->produtoModel->buscarPorId($idProduto);
        if (!$produtoAtual) {
            return $this->erro('Produto não encontrado.', 404);
        }

        $this->produtoModel->atualizar(
            $idProduto,
            $idCategoria,
            $nome,
            $descricao,
            $preco,
            $estoque,
            $permitePedido,
            $ativo
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Produto atualizado com sucesso.',
            'id_produto' => $idProduto,
        ];
    }

    public function excluir(int $idProduto): array
    {
        if ($idProduto <= 0) {
            return $this->erro('Produto inválido.', 422);
        }

        $produto = $this->produtoModel->buscarPorId($idProduto);
        if (!$produto) {
            return $this->erro('Produto não encontrado.', 404);
        }

        if ($this->produtoModel->temPedidos($idProduto)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Este produto possui histórico de pedidos e não pode ser excluído. Desative-o no cadastro para mantê-lo fora do catálogo.',
                'status' => 409,
            ];
        }

        $imagens = $produto['imagens'] ?? [];
        $this->produtoModel->excluir($idProduto);

        foreach ($imagens as $imagem) {
            $this->apagarArquivoSeLocal($imagem['caminho'] ?? '');
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Produto excluído com sucesso.',
        ];
    }

    public function removerImagens(int $idProduto, array $idsImagens): array
    {
        $produto = $this->produtoModel->buscarPorId($idProduto);
        if (!$produto) {
            return $this->erro('Produto não encontrado.', 404);
        }

        $removidas = 0;

        foreach ($idsImagens as $idImagem) {
            $idImagem = (int) $idImagem;
            if ($idImagem <= 0) {
                continue;
            }

            $imagem = $this->produtoModel->removerImagem($idImagem, $idProduto);
            if ($imagem) {
                $this->apagarArquivoSeLocal($imagem['caminho'] ?? '');
                $removidas++;
            }
        }

        if (!$this->produtoModel->imagemPrincipalExiste($idProduto)) {
            $this->produtoModel->tornarPrimeiraImagemPrincipal($idProduto);
        }

        return [
            'sucesso' => true,
            'mensagem' => $removidas > 0
                ? $removidas . ' imagem(ns) removida(s).'
                : 'Nenhuma imagem foi removida.',
        ];
    }

    public function definirPrincipal(int $idProduto, int $idImagem): array
    {
        if ($idProduto <= 0 || $idImagem <= 0) {
            return $this->erro('Produto ou imagem inválidos.', 422);
        }

        if (!$this->produtoModel->definirImagemPrincipal($idProduto, $idImagem)) {
            return $this->erro('Imagem não pertence ao produto informado.', 404);
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Imagem principal atualizada.',
        ];
    }

    public function adicionarUploads(int $idProduto, array $arquivos): array
    {
        $produto = $this->produtoModel->buscarPorId($idProduto);
        if (!$produto) {
            return $this->erro('Produto não encontrado.', 404);
        }

        $arquivos = $this->normalizarArquivos($arquivos);
        if (!$arquivos) {
            return [
                'sucesso' => true,
                'mensagem' => 'Nenhuma nova imagem enviada.',
            ];
        }

        $temPrincipal = $this->produtoModel->imagemPrincipalExiste($idProduto);
        $salvas = 0;
        $arquivosCriados = [];

        $this->produtoModel->iniciarTransacao();

        try {
            foreach ($arquivos as $arquivo) {
                $caminho = $this->salvarImagem($arquivo);
                $this->produtoModel->adicionarImagem($idProduto, $caminho, !$temPrincipal && $salvas === 0);
                $arquivosCriados[] = $caminho;
                $salvas++;
            }

            $this->produtoModel->confirmarTransacao();
        } catch (Throwable $e) {
            $this->produtoModel->cancelarTransacao();

            foreach ($arquivosCriados as $caminho) {
                $this->apagarArquivoSeLocal($caminho);
            }

            throw $e;
        }

        return [
            'sucesso' => true,
            'mensagem' => $salvas . ' imagem(ns) adicionada(s).',
        ];
    }

    private function normalizarDecimal(mixed $valor): ?float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }

        $texto = trim((string) $valor);
        $texto = str_replace('R$', '', $texto);
        $texto = preg_replace('/\s+/', '', $texto) ?? '';

        if (str_contains($texto, ',') && str_contains($texto, '.')) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } elseif (str_contains($texto, ',')) {
            $texto = str_replace(',', '.', $texto);
        }

        return $texto !== '' && is_numeric($texto) ? (float) $texto : null;
    }

    private function normalizarBoolean(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        return in_array((string) $valor, ['1', 'true', 'on', 'yes'], true);
    }

    private function gerarSlugUnico(string $nome): string
    {
        $base = $this->slugify($nome);
        $base = $base !== '' ? $base : 'produto';

        $slug = $base;
        $numero = 2;

        while ($this->produtoModel->slugExiste($slug)) {
            $slug = $base . '-' . $numero;
            $numero++;
        }

        return $slug;
    }

    private function slugify(string $texto): string
    {
        $texto = trim(mb_strtolower($texto, 'UTF-8'));
        $convertido = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($convertido !== false) {
            $texto = $convertido;
        }

        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto) ?? '';
        $texto = trim($texto, '-');

        return substr($texto, 0, 140);
    }

    private function normalizarArquivos(array $arquivos): array
    {
        $lista = [];

        if (isset($arquivos['name']) && is_array($arquivos['name'])) {
            $total = count($arquivos['name']);

            for ($i = 0; $i < $total; $i++) {
                $erro = (int) ($arquivos['error'][$i] ?? UPLOAD_ERR_NO_FILE);
                if ($erro === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $lista[] = [
                    'name' => $arquivos['name'][$i] ?? '',
                    'type' => $arquivos['type'][$i] ?? '',
                    'tmp_name' => $arquivos['tmp_name'][$i] ?? '',
                    'error' => $erro,
                    'size' => $arquivos['size'][$i] ?? 0,
                ];
            }
        } elseif (isset($arquivos['name'])) {
            if ((int) ($arquivos['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $lista[] = $arquivos;
            }
        }

        if (count($lista) > 8) {
            throw new RuntimeException('Você pode enviar no máximo 8 imagens por vez.');
        }

        return $lista;
    }

    private function salvarImagem(array $arquivo): string
    {
        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Uma das imagens não pôde ser enviada.');
        }

        if (($arquivo['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new RuntimeException('Cada imagem deve ter no máximo 5 MB.');
        }

        $tmp = $arquivo['tmp_name'] ?? '';
        if (!is_uploaded_file($tmp)) {
            throw new RuntimeException('Upload de imagem inválido.');
        }

        $info = @getimagesize($tmp);
        if ($info === false) {
            throw new RuntimeException('Envie apenas arquivos de imagem válidos.');
        }

        $mime = $info['mime'] ?? '';
        $extensoes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensoes[$mime])) {
            throw new RuntimeException('Formato inválido. Use JPG, PNG ou WEBP.');
        }

        $diretorio = __DIR__ . '/../public/uploads/produtos';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível criar o diretório de imagens.');
        }

        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
        $destino = $diretorio . DIRECTORY_SEPARATOR . $nomeArquivo;

        if (!move_uploaded_file($tmp, $destino)) {
            throw new RuntimeException('Não foi possível salvar a imagem enviada.');
        }

        return 'uploads/produtos/' . $nomeArquivo;
    }

    private function apagarArquivoSeLocal(string $caminho): void
    {
        $caminho = ltrim(str_replace('\\', '/', $caminho), '/');

        if (!str_starts_with($caminho, 'uploads/produtos/')) {
            return;
        }

        $raiz = realpath(__DIR__ . '/../public/uploads/produtos');
        if ($raiz === false) {
            return;
        }

        $arquivo = realpath(__DIR__ . '/../public/' . $caminho);
        if ($arquivo === false) {
            return;
        }

        $prefixo = rtrim(str_replace('\\', '/', $raiz), '/') . '/';
        $arquivoNormalizado = str_replace('\\', '/', $arquivo);

        if (str_starts_with($arquivoNormalizado, $prefixo) && is_file($arquivo)) {
            @unlink($arquivo);
        }
    }

    private function erro(string $mensagem, int $status): array
    {
        return [
            'sucesso' => false,
            'mensagem' => $mensagem,
            'status' => $status,
        ];
    }

    /** @param array<string, mixed> $produto */
    private function normalizarProduto(array $produto): array
    {
        $produto['id_produto'] = (int) $produto['id_produto'];
        $produto['id_categoria'] = (int) $produto['id_categoria'];
        $produto['estoque'] = (int) $produto['estoque'];
        $produto['permite_pedido'] = (bool) $produto['permite_pedido'];
        $produto['preco'] = $produto['permite_pedido'] && $produto['preco'] !== null
            ? (float) $produto['preco']
            : null;
        $produto['ativo'] = (bool) $produto['ativo'];

        if (isset($produto['imagens']) && is_array($produto['imagens'])) {
            $produto['imagens'] = array_map(static function (array $imagem): array {
                $imagem['id_imagem'] = (int) $imagem['id_imagem'];
                $imagem['id_produto'] = (int) $imagem['id_produto'];
                $imagem['principal'] = (bool) $imagem['principal'];
                return $imagem;
            }, $produto['imagens']);
        }

        return $produto;
    }
}
