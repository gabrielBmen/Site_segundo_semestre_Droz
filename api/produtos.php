<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/ProdutoModel.php';
require_once __DIR__ . '/../controllers/ProdutoController.php';

exigirAdminJson();

try {
    $produtoModel = new ProdutoModel($pdo);
    $controller = new ProdutoController($produtoModel);
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET') {
        $acao = $_GET['acao'] ?? 'listar';

        if ($acao === 'categorias') {
            $categorias = array_map(static function (array $categoria): array {
                return [
                    'id_categoria' => (int) $categoria['id_categoria'],
                    'nome' => (string) $categoria['nome'],
                    'ativo' => (bool) $categoria['ativo'],
                ];
            }, $produtoModel->listarCategorias());
            echo json_encode([
                'sucesso' => true,
                'dados' => $categorias,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($acao === 'buscar') {
            $resultado = $controller->buscar((int) ($_GET['id'] ?? 0));
            $status = $resultado['status'] ?? 200;
            unset($resultado['status']);
            http_response_code($status);
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $busca = isset($_GET['busca']) ? trim((string) $_GET['busca']) : null;
        $idCategoria = isset($_GET['id_categoria']) ? (int) $_GET['id_categoria'] : null;
        echo json_encode($controller->listar($busca, $idCategoria), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($metodo !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Método não permitido.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Sua sessão expirou. Atualize a página e tente novamente.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $acao = (string) ($_POST['acao'] ?? '');

    switch ($acao) {
        case 'salvar':
            $resultado = $controller->salvar($_POST);

            if (($resultado['sucesso'] ?? false)) {
                $idProduto = (int) ($resultado['id_produto'] ?? 0);

                if ($idProduto > 0 && isset($_POST['remover_imagens'])) {
                    $idsImagens = $_POST['remover_imagens'];
                    if (!is_array($idsImagens)) {
                        $idsImagens = [$idsImagens];
                    }

                    $remo = $controller->removerImagens($idProduto, $idsImagens);
                    if (($remo['sucesso'] ?? false) && !empty($remo['mensagem'])) {
                        $resultado['mensagem'] .= ' ' . $remo['mensagem'];
                    }
                }

                if ($idProduto > 0 && !empty($_FILES['imagens'])) {
                    $upload = $controller->adicionarUploads($idProduto, $_FILES['imagens']);
                    if (!($upload['sucesso'] ?? false)) {
                        throw new RuntimeException($upload['mensagem'] ?? 'Não foi possível adicionar as imagens.');
                    }
                    if (!empty($upload['mensagem'])) {
                        $resultado['mensagem'] .= ' ' . $upload['mensagem'];
                    }
                }

                $principalId = (int) ($_POST['imagem_principal_id'] ?? 0);
                if ($idProduto > 0 && $principalId > 0) {
                    $principal = $controller->definirPrincipal($idProduto, $principalId);
                    if (!($principal['sucesso'] ?? false)) {
                        throw new RuntimeException($principal['mensagem'] ?? 'Não foi possível definir a imagem principal.');
                    }
                }
            }
            break;

        case 'excluir':
            $resultado = $controller->excluir((int) ($_POST['id_produto'] ?? 0));
            break;

        case 'remover_imagens':
            $ids = $_POST['ids_imagens'] ?? [];
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            $resultado = $controller->removerImagens(
                (int) ($_POST['id_produto'] ?? 0),
                $ids
            );
            break;

        case 'definir_principal':
            $resultado = $controller->definirPrincipal(
                (int) ($_POST['id_produto'] ?? 0),
                (int) ($_POST['id_imagem'] ?? 0)
            );
            break;

        case 'adicionar_imagens':
            $resultado = $controller->adicionarUploads(
                (int) ($_POST['id_produto'] ?? 0),
                $_FILES['imagens'] ?? []
            );
            break;

        default:
            $resultado = [
                'sucesso' => false,
                'mensagem' => 'Ação de produto inválida.',
                'status' => 400,
            ];
            break;
    }

    $status = $resultado['status'] ?? (($resultado['sucesso'] ?? false) ? 200 : 422);
    unset($resultado['status']);
    http_response_code($status);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    http_response_code(422);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível concluir a operação com o produto.',
    ], JSON_UNESCAPED_UNICODE);
}
