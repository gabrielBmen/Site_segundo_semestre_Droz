# Projeto Web - DROZ Robótica

## Estrutura


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


## Autenticação

O módulo de login está documentado em `README_LOGIN.md`. O visitante permanece livre para navegar, enquanto preços, detalhes completos das máquinas e contato exigem sessão.
