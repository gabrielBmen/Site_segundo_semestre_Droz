# Projeto Web - DROZ Robótica

## Estrutura
- `index.php`
- `sobre.php`
- `produtos.php`
- `servicos.php`
- `contato.php`
- `includes/header.php`
- `includes/footer.php`
- `includes/funcoes.php`
- `config/conexao.php`
- `assets/css/style.css`
- `database/droz_robotica.sql`

## Observações
- A conexão em `config/conexao.php` usa IP fixo, como exigido na rubrica.
- O banco possui tabelas com chave primária, estrangeira e relacionamento N:N.
- O site usa template PHP com header e footer.
- O catálogo usa arrays e funções para atender aos critérios de lógica.
- O conteúdo institucional foi baseado em informações públicas da empresa.

## Execução
1. Importe `database/droz_robotica.sql` no MySQL.
2. Ajuste o IP em `config/conexao.php` para o servidor do banco.
3. Coloque o projeto no Apache.
4. Configure o Apache para escutar na porta 8080.
5. Acesse via `http://drozrobotica.local:8080`.
