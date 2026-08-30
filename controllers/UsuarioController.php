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

        $digitosTelefone = preg_replace('/\D+/', '', $telefone) ?? '';
        if (strlen($digitosTelefone) < 10 || strlen($digitosTelefone) > 15) {
            return [
                'sucesso' => false,
                'mensagem' => 'Informe um telefone válido com DDD.',
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

    public function listarParaAdmin(): array
    {
        return $this->usuarioModel->listarParaAdmin();
    }

    public function atualizarProprioPerfil(
        int $idUsuario,
        string $telefone,
        ?array $fotoPerfil,
        bool $removerFoto
    ): array {
        $perfil = $this->usuarioModel->buscarPerfilPorId($idUsuario);
        if (!$perfil || !(bool) $perfil['ativo']) {
            return ['sucesso' => false, 'mensagem' => 'Conta não encontrada ou inativa.'];
        }

        $telefone = trim($telefone);
        $digitosTelefone = preg_replace('/\D+/', '', $telefone) ?? '';
        if ($perfil['tipo'] === 'cliente' && (strlen($digitosTelefone) < 10 || strlen($digitosTelefone) > 15)) {
            return ['sucesso' => false, 'mensagem' => 'Informe um telefone válido com DDD.'];
        }

        $fotoAnterior = (string) ($perfil['foto_perfil'] ?? '');
        $novaFoto = null;
        $alterarFoto = $removerFoto;

        try {
            if ($fotoPerfil && (int) ($fotoPerfil['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $novaFoto = $this->salvarFotoPerfil($fotoPerfil);
                $alterarFoto = true;
            }

            if ($perfil['tipo'] === 'cliente'
                && !$this->usuarioModel->atualizarTelefoneDoCliente($idUsuario, $telefone !== '' ? $telefone : null)) {
                throw new RuntimeException('Não foi possível atualizar o telefone.');
            }

            if ($alterarFoto) {
                if (!$this->usuarioModel->atualizarFotoPerfil(
                    $idUsuario,
                    $removerFoto && $novaFoto === null ? null : $novaFoto
                )) {
                    throw new RuntimeException('Não foi possível atualizar a foto do perfil.');
                }
                if ($fotoAnterior !== '' && $fotoAnterior !== $novaFoto) {
                    $this->apagarFotoPerfilLocal($fotoAnterior);
                }
            }

            return ['sucesso' => true, 'mensagem' => 'Perfil atualizado com segurança.'];
        } catch (Throwable $e) {
            if ($novaFoto !== null) $this->apagarFotoPerfilLocal($novaFoto);
            return [
                'sucesso' => false,
                'mensagem' => $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Não foi possível atualizar o perfil.',
            ];
        }
    }

    public function buscarParaAdmin(int $idUsuario): ?array
    {
        return $this->usuarioModel->buscarCompletoPorId($idUsuario);
    }

    /** @param array<string, mixed> $dados */
    public function salvarPeloAdmin(array $dados, int $idAdminAtual): array
    {
        $idUsuario = (int) ($dados['id_usuario'] ?? 0);
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = mb_strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $tipo = (string) ($dados['tipo'] ?? 'cliente');
        $telefone = trim((string) ($dados['telefone'] ?? ''));
        $ativo = isset($dados['ativo']) && (string) $dados['ativo'] === '1';

        if (!Usuario::validarNome($nome)) {
            return ['sucesso' => false, 'mensagem' => 'Informe um nome entre 3 e 120 caracteres.'];
        }
        if (!Usuario::validarEmail($email)) {
            return ['sucesso' => false, 'mensagem' => 'Informe um e-mail válido.'];
        }
        if (!in_array($tipo, ['cliente', 'admin'], true)) {
            return ['sucesso' => false, 'mensagem' => 'Selecione um tipo de usuário válido.'];
        }
        if ($idUsuario === 0 && !Usuario::validarSenha($senha)) {
            return ['sucesso' => false, 'mensagem' => 'A senha deve ter entre 8 e 72 caracteres.'];
        }
        if ($idUsuario > 0 && $senha !== '' && !Usuario::validarSenha($senha)) {
            return ['sucesso' => false, 'mensagem' => 'A nova senha deve ter entre 8 e 72 caracteres.'];
        }

        $emailExistente = $this->usuarioModel->buscarPorEmail($email);
        if ($emailExistente && (int) $emailExistente['id_usuario'] !== $idUsuario) {
            return ['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.'];
        }

        try {
            if ($idUsuario === 0) {
                $this->usuarioModel->criarGerenciado($nome, $email, $senha, $tipo, $ativo, $telefone);
                return ['sucesso' => true, 'mensagem' => 'Usuário criado com sucesso.'];
            }

            $atual = $this->usuarioModel->buscarCompletoPorId($idUsuario);
            if (!$atual) {
                return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado.'];
            }
            if ($idUsuario === $idAdminAtual && ($tipo !== 'admin' || !$ativo)) {
                return ['sucesso' => false, 'mensagem' => 'Você não pode remover seu próprio acesso administrativo.'];
            }
            $removerAdminAtivo = $atual['tipo'] === 'admin'
                && (bool) $atual['ativo']
                && ($tipo !== 'admin' || !$ativo);
            if ($removerAdminAtivo && $this->usuarioModel->contarAdminsAtivos() <= 1) {
                return ['sucesso' => false, 'mensagem' => 'Mantenha ao menos um administrador ativo.'];
            }
            if ($atual['tipo'] === 'cliente' && $tipo === 'admin' && $this->usuarioModel->possuiPedidos($idUsuario)) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Este cliente possui pedidos e não pode ser convertido em administrador. Crie outro usuário administrativo.',
                ];
            }

            $this->usuarioModel->atualizarGerenciado(
                $idUsuario,
                $nome,
                $email,
                $senha !== '' ? $senha : null,
                $tipo,
                $ativo,
                $telefone
            );
            return ['sucesso' => true, 'mensagem' => 'Usuário atualizado com sucesso.'];
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return ['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.'];
            }
            throw $e;
        }
    }

    public function excluirPeloAdmin(int $idUsuario, int $idAdminAtual): array
    {
        $usuario = $this->usuarioModel->buscarCompletoPorId($idUsuario);
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado.'];
        }
        if ($idUsuario === $idAdminAtual) {
            return ['sucesso' => false, 'mensagem' => 'Você não pode excluir seu próprio usuário.'];
        }
        if ($this->usuarioModel->possuiPedidos($idUsuario)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Este usuário possui histórico de pedidos. Desative-o para preservar os registros.',
            ];
        }
        if ($usuario['tipo'] === 'admin' && (bool) $usuario['ativo'] && $this->usuarioModel->contarAdminsAtivos() <= 1) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível excluir o único administrador ativo.'];
        }

        return $this->usuarioModel->excluir($idUsuario)
            ? ['sucesso' => true, 'mensagem' => 'Usuário excluído com sucesso.']
            : ['sucesso' => false, 'mensagem' => 'Usuário não encontrado.'];
    }

    private function salvarFotoPerfil(array $arquivo): string
    {
        if ((int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('A foto não pôde ser enviada.');
        }
        if ((int) ($arquivo['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new RuntimeException('A foto deve ter no máximo 2 MB.');
        }

        $temporario = (string) ($arquivo['tmp_name'] ?? '');
        if ($temporario === '' || !is_uploaded_file($temporario)) {
            throw new RuntimeException('O arquivo enviado não é válido.');
        }

        $informacoes = @getimagesize($temporario);
        $mime = is_array($informacoes) ? (string) ($informacoes['mime'] ?? '') : '';
        $extensoes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($extensoes[$mime])) {
            throw new RuntimeException('Use uma foto JPG, PNG ou WEBP.');
        }

        $diretorio = __DIR__ . '/../public/uploads/perfis';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível preparar o diretório da foto.');
        }

        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
        if (!move_uploaded_file($temporario, $diretorio . DIRECTORY_SEPARATOR . $nomeArquivo)) {
            throw new RuntimeException('Não foi possível salvar a foto.');
        }

        return 'uploads/perfis/' . $nomeArquivo;
    }

    private function apagarFotoPerfilLocal(string $caminho): void
    {
        $caminho = ltrim(str_replace('\\', '/', $caminho), '/');
        if (!str_starts_with($caminho, 'uploads/perfis/')) return;

        $raiz = realpath(__DIR__ . '/../public/uploads/perfis');
        $arquivo = realpath(__DIR__ . '/../public/' . $caminho);
        if ($raiz === false || $arquivo === false || !is_file($arquivo)) return;

        $raizNormalizada = rtrim(str_replace('\\', '/', $raiz), '/') . '/';
        $arquivoNormalizado = str_replace('\\', '/', $arquivo);
        if (str_starts_with($arquivoNormalizado, $raizNormalizada)) @unlink($arquivo);
    }
}
