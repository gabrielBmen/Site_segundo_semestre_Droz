# DROZ Robótica

Sistema web para apresentação e operação comercial da DROZ Robótica. O projeto reúne site institucional, catálogo de produtos, solicitações de orçamento, pedidos online, área privada do cliente e painel administrativo.

Foi desenvolvido com PHP, MariaDB/MySQL, Bootstrap 5 e TypeScript, seguindo uma separação em classes, models, controllers, APIs e páginas.

## Sumário

- [O que o sistema faz](#o-que-o-sistema-faz)
- [Tecnologias utilizadas](#tecnologias-utilizadas)
- [Requisitos](#requisitos)
- [Como executar no XAMPP](#como-executar-no-xampp)
- [Configuração do banco](#configuração-do-banco)
- [Configuração do endereço local](#configuração-do-endereço-local)
- [Acesso ao sistema](#acesso-ao-sistema)
- [Partes do site](#partes-do-site)
- [Regras de negócio](#regras-de-negócio)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Banco de dados](#banco-de-dados)
- [TypeScript](#typescript)
- [Testes e validações](#testes-e-validações)
- [Problemas comuns](#problemas-comuns)
- [Publicação](#publicação)

## O que o sistema faz

O sistema atende três tipos de acesso:

1. **Visitante:** consulta páginas institucionais e o catálogo, mas precisa entrar para visualizar informações protegidas e realizar operações.
2. **Cliente:** compra produtos permitidos, solicita orçamento, acompanha pedidos e gerencia o próprio perfil.
3. **Administrador:** gerencia produtos, categorias, usuários, pedidos e orçamentos, além de acompanhar indicadores de vendas.

Principais recursos:

- autenticação por sessão e separação entre cliente e administrador;
- cadastro de cliente com nome, e-mail, telefone e senha;
- catálogo com busca e filtro por categoria;
- produtos ativos ou inativos;
- produtos disponíveis para pedido online ou somente para orçamento;
- carrinho e finalização de pedido;
- controle e baixa de estoque;
- solicitação de orçamento vinculada opcionalmente a um produto;
- contato direto pelo WhatsApp na administração;
- histórico privado de pedidos do cliente;
- foto de perfil opcional e telefone editável;
- dashboard com faturamento, pedidos, itens vendidos, média mensal, status e ranking;
- CRUD de produtos, categorias e usuários;
- controle de status dos pedidos;
- proteção CSRF em operações administrativas e de perfil;
- senhas armazenadas com `password_hash` e verificadas com `password_verify`.

## Tecnologias utilizadas

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2+ |
| Banco | MariaDB ou MySQL |
| Servidor local | Apache pelo XAMPP |
| Frontend | HTML5, CSS3 e Bootstrap 5.3 |
| Interações | TypeScript compilado para JavaScript |
| Banco no PHP | PDO com prepared statements |
| Ícones | Bootstrap Icons |
| Fonte | Inter |

## Requisitos

Para executar o projeto localmente:

- Windows com XAMPP instalado;
- Apache e MySQL/MariaDB;
- PHP 8.2 ou superior;
- extensões PHP `pdo_mysql` e `mbstring` habilitadas;
- navegador atualizado;
- DBeaver ou phpMyAdmin para administrar o banco, se desejar;
- Git, somente se o projeto for clonado do GitHub;
- Node.js e npm, somente para modificar/recompilar o TypeScript.

Os arquivos JavaScript compilados já estão em `public/assets/js`. Portanto, **Node.js não é necessário apenas para abrir e usar o site**.

## Como executar no XAMPP

### 1. Obter o projeto

Clone o repositório ou copie a pasta manualmente para o `htdocs`:

```powershell
cd C:\xampp\htdocs
git clone <URL_DO_REPOSITORIO> Droz-Robotica
```

O resultado esperado é:

```text
C:\xampp\htdocs\Droz-Robotica
```

Se o projeto foi baixado em ZIP, extraia a pasta nesse mesmo local.

### 2. Iniciar o XAMPP

Abra o painel do XAMPP e inicie:

- **Apache**;
- **MySQL**.

O DBeaver não substitui o MySQL do XAMPP. Ele apenas se conecta ao banco que já está sendo executado pelo MySQL.

### 3. Criar o banco

Para uma instalação nova, importe:

```text
database/droz_robotica.sql
```

Esse arquivo cria o banco `droz_robotica`, todas as tabelas, relacionamentos, índices, triggers, função, view, Stored Procedure, categorias, produtos iniciais e um usuário administrador.

> **Atenção:** `droz_robotica.sql` contém `DROP DATABASE`. Ele apaga e recria o banco. Não o execute sobre um banco com dados importantes sem fazer backup.

### 4. Conferir a conexão

Abra `config/config.php` e ajuste os dados de acordo com seu MySQL:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'droz_robotica');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
```

No XAMPP padrão, o usuário costuma ser `root` e a senha fica vazia. Em produção, utilize um usuário próprio e uma senha forte.

### 5. Configurar o endereço local

O projeto usa `public` como raiz pública. Configure o VirtualHost descrito na próxima seção para acessar:

```text
http://drozrobotica.local:8080
```

### 6. Abrir o sistema

Depois de reiniciar o Apache, abra:

```text
http://drozrobotica.local:8080
```

## Configuração do banco

### Importar pelo DBeaver

1. Inicie o MySQL no XAMPP.
2. Abra a conexão `localhost` no DBeaver.
3. Abra um novo editor SQL.
4. Carregue `database/droz_robotica.sql`.
5. Execute o script completo.
6. Atualize a lista de bancos e confirme a existência de `droz_robotica`.

Configuração comum da conexão:

| Campo | Valor padrão |
|---|---|
| Host | `localhost` |
| Porta | `3306` |
| Banco | `droz_robotica` |
| Usuário | `root` |
| Senha | vazia no XAMPP padrão |

### Importar pelo terminal

No PowerShell:

```powershell
cd C:\xampp\htdocs\Droz-Robotica
C:\xampp\mysql\bin\mysql.exe -u root < database\droz_robotica.sql
```

Se o MySQL possuir senha, use `-p`:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -p < database\droz_robotica.sql
```

### Definir sua senha administrativa

O script cria o usuário:

```text
admin@drozrobotica.com
```

Antes de usar ou publicar o projeto, gere seu próprio hash de senha:

```powershell
C:\xampp\php\php.exe -r "echo password_hash('SuaSenhaForte123!', PASSWORD_DEFAULT), PHP_EOL;"
```

Copie o resultado e execute no DBeaver ou phpMyAdmin:

```sql
USE droz_robotica;

UPDATE usuarios
SET senha = 'COLE_AQUI_O_HASH_GERADO'
WHERE email = 'admin@drozrobotica.com';
```

Não coloque a senha verdadeira ou um hash de produção no README ou em commits públicos.

### Dados de demonstração da dashboard

Uma instalação nova não possui pedidos reais. Por isso, a dashboard inicia sem vendas. Para uma apresentação acadêmica, é possível executar opcionalmente:

```text
database/seed_dashboard.sql
```

Esse script adiciona cliente, pedido e itens de demonstração. Não use dados fictícios em produção.

### Atualizar um banco já existente

Se o banco foi criado por uma versão anterior do projeto, faça backup e execute, nesta ordem:

1. `database/atualizacao_comercio.sql` — adiciona recursos do fluxo de pedidos;
2. `database/atualizacao_orcamentos.sql` — adiciona o vínculo opcional entre orçamento e produto;
3. `database/atualizacao_rubrica.sql` — atualiza índices e objetos analíticos da dashboard;
4. `database/atualizacao_perfil.sql` — adiciona a foto opcional do usuário.

Em uma instalação nova feita com `droz_robotica.sql`, não é necessário executar esses quatro arquivos separadamente.

## Configuração do endereço local

Os links do sistema consideram que a pasta `public` é o `DocumentRoot`. Essa configuração também impede que pastas internas, como `config` e `database`, sejam expostas pelo Apache.

### 1. Arquivo `hosts`

Abra o Bloco de Notas como administrador e edite:

```text
C:\Windows\System32\drivers\etc\hosts
```

Adicione:

```text
127.0.0.1 drozrobotica.local
```

### 2. VirtualHost do Apache

Abra:

```text
C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

Adicione no final, alterando a pasta se você utilizou outro nome:

```apache
<VirtualHost *:8080>
    ServerName drozrobotica.local
    DocumentRoot "C:/xampp/htdocs/Droz-Robotica/public"

    <Directory "C:/xampp/htdocs/Droz-Robotica/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Confirme também que o Apache está ouvindo a porta `8080`. No arquivo `C:\xampp\apache\conf\httpd.conf`, deve existir:

```apache
Listen 8080
```

Não adicione uma segunda linha `Listen 8080` se ela já existir.

### 3. URL configurada na aplicação

Em `config/config.php`, mantenha o endereço igual ao VirtualHost:

```php
define('BASE_URL', 'http://drozrobotica.local:8080');
```

Reinicie o Apache depois de qualquer alteração.

## Acesso ao sistema

### Cliente

- cadastro: `http://drozrobotica.local:8080/cadastro.php`;
- login: `http://drozrobotica.local:8080/login.php`;
- área privada: `http://drozrobotica.local:8080/minha-conta.php`.

Qualquer visitante pode criar uma conta de cliente. O telefone e uma senha de no mínimo oito caracteres são obrigatórios.

### Administrador

- login administrativo: `http://drozrobotica.local:8080/admin/login.php`;
- dashboard: `http://drozrobotica.local:8080/admin/`.

No site público, a dashboard fica dentro do menu lateral da conta e só aparece quando um administrador está autenticado.

## Partes do site

### Área pública

| Página | Arquivo | Função |
|---|---|---|
| Home | `public/index.php` | Apresenta a empresa, diferenciais e produtos em destaque. |
| Sobre | `public/sobre.php` | Exibe informações institucionais sobre a DROZ Robótica. |
| Serviços | `public/servicos.php` | Apresenta serviços e soluções de automação. |
| Catálogo | `public/produtos.php` | Lista produtos ativos, com busca e filtro por categoria. |
| Produto | `public/produto.php` | Mostra descrição, imagens, preço protegido e ação comercial adequada. |
| Orçamento | `public/contato.php` | Grava uma solicitação geral ou vinculada a um produto. |
| Cadastro | `public/cadastro.php` | Cria uma conta de cliente. |
| Login | `public/login.php` | Autentica o cliente e preserva o destino solicitado. |

O cabeçalho deixa expostos somente os links principais: Home, Sobre, Serviços e Catálogo. Carrinho, orçamento, histórico, dados pessoais, dashboard e saída ficam organizados no menu hambúrguer lateral.

### Área do cliente

| Página | Arquivo | Função |
|---|---|---|
| Carrinho | `public/carrinho.php` | Exibe itens, quantidades, subtotais e total antes da confirmação. |
| Minha conta | `public/minha-conta.php` | Exibe dados privados, data de criação, telefone, foto e contadores. |
| Histórico | seção de `public/minha-conta.php` | Lista somente os pedidos do usuário autenticado, com itens, valores, datas e status. |
| Logout | `public/logout.php` | Encerra a sessão atual. |

O cliente não informa um ID pela URL para consultar o histórico. O usuário é obtido da sessão, e os pedidos são filtrados no banco pelo relacionamento entre `usuarios`, `clientes` e `pedidos`.

### Área administrativa

Todas as páginas em `public/admin` exigem uma sessão com perfil `admin`.

| Seção | Arquivo | Função |
|---|---|---|
| Dashboard | `public/admin/index.php` | Analisa vendas por 7 dias, 30 dias ou datas específicas. |
| Produtos | `public/admin/produtos.php` | Cadastra, edita, ativa, desativa e exclui produtos quando permitido. |
| Categorias | `public/admin/categorias.php` | CRUD de categorias e contagem de produtos associados. |
| Pedidos | `public/admin/pedidos.php` | Exibe cliente, itens, data, valor e permite alterar status. |
| Orçamentos | `public/admin/orcamentos.php` | Exibe cliente, contato, interesse, mensagem e produto vinculado. |
| Usuários | `public/admin/usuarios.php` | CRUD de clientes e administradores, telefone, perfil e status. |
| Configurações | `public/admin/configuracoes.php` | Resume as tecnologias e o ambiente da aplicação. |

A dashboard permite:

- selecionar últimos 7 dias, últimos 30 dias ou intervalo personalizado;
- buscar por pedido, cliente ou produto;
- filtrar pelo status do pedido;
- escolher a quantidade de itens por página;
- acompanhar faturamento no período;
- acompanhar número de pedidos e itens vendidos;
- visualizar faturamento médio mensal;
- comparar pedidos por status;
- consultar produtos mais vendidos;
- navegar pelos registros analíticos paginados.

## Regras de negócio

### Produtos

Cada produto possui duas configurações independentes:

- `ativo`: define se aparece no catálogo público;
- `permite_pedido`: define se pode ser comprado online.

Com isso:

- produto ativo e comprável exibe a ação de adicionar ao pedido;
- produto ativo e não comprável exibe solicitação de orçamento;
- produto inativo não aparece no catálogo público;
- máquinas e serviços podem permanecer no catálogo sem aceitar compra online;
- peças e consumíveis podem aceitar pedido, desde que tenham estoque.

### Pedidos

- somente clientes autenticados podem finalizar pedidos;
- somente produtos ativos, compráveis e com estoque podem entrar no pedido;
- o estoque é validado novamente na finalização;
- pedido e itens são gravados dentro de uma transação;
- o estoque é reduzido depois da confirmação;
- os status permitidos são `pendente`, `aprovado`, `concluido` e `cancelado`;
- pedidos cancelados não entram no faturamento do cliente nem da dashboard.

### Orçamentos

- podem ser gerais ou vinculados a um produto;
- ficam armazenados na tabela `contatos`;
- o admin visualiza produto, interesse e mensagem;
- o botão do WhatsApp utiliza o telefone informado pelo cliente.

### Exclusões e preservação de histórico

- categoria usada por produto não é excluída;
- produto presente em pedido não é excluído;
- usuário com pedidos não é excluído, podendo ser desativado;
- o último administrador ativo não pode ser removido;
- um administrador não pode retirar o próprio acesso administrativo.

## Estrutura do projeto

```text
Droz-Robotica/
├── api/                     Endpoints JSON usados pelo TypeScript
├── classes/                 Entidades e validações de domínio
├── config/                  Constantes e conexão PDO
├── controllers/             Regras e validações entre páginas e models
├── database/                Instalação, atualizações e dados de demonstração
├── includes/                Autenticação, CSRF, funções e layout público
├── models/                  Consultas, persistência e transações
├── public/                  Única pasta que deve ficar exposta pelo Apache
│   ├── admin/               Dashboard e páginas administrativas
│   ├── api/                 Entradas públicas que encaminham para as APIs internas
│   ├── assets/              CSS, JavaScript, imagens e estilos do admin
│   ├── uploads/             Imagens de produtos e fotos de perfil
│   └── *.php                Páginas públicas e privadas do cliente
├── typescript/
│   ├── src/                 Código TypeScript editável
│   ├── package.json         Scripts e dependências de desenvolvimento
│   └── tsconfig.json        Configuração do compilador
├── .gitignore               Arquivos que não devem ir ao GitHub
└── README.md                Documentação do projeto
```

### Responsabilidade das camadas

- **classes:** concentra validações simples do domínio, como nome, e-mail e senha;
- **models:** executa SQL por PDO e realiza transações;
- **controllers:** valida entradas e aplica regras antes de chamar os models;
- **api:** recebe JSON, valida método/permissão e devolve respostas JSON;
- **includes:** compartilha sessão, autorização, CSRF, cabeçalho, rodapé e funções;
- **public:** contém as páginas acessíveis pelo navegador;
- **typescript/src:** contém os fontes das interações assíncronas;
- **public/assets/js:** contém o JavaScript compilado que o navegador executa.

## Banco de dados

### Tabelas

| Tabela | Responsabilidade |
|---|---|
| `usuarios` | Login, perfil, status, foto e tipo de acesso. |
| `clientes` | Dados comerciais do cliente, incluindo telefone. |
| `contatos` | Solicitações de orçamento e mensagens. |
| `categorias` | Organização do catálogo. |
| `produtos` | Dados, preço, estoque, status e permissão de pedido. |
| `imagens_produto` | Galeria e imagem principal dos produtos. |
| `pedidos` | Cabeçalho do pedido, cliente, data, valor e status. |
| `pedido_produto` | Relacionamento N:N entre pedidos e produtos, com quantidade e preço unitário. |

### Objetos avançados

- `fn_calcular_valor_item`: calcula quantidade × preço unitário;
- `vw_pedido_itens_analiticos`: consolida pedido, cliente, produto e categoria;
- `sp_dashboard_indicadores`: entrega métricas, status, ranking e paginação para a dashboard;
- triggers de validação: impedem valores negativos em produtos, itens e pedidos;
- índices: aceleram consultas por categoria, cliente, status, data e permissão de pedido;
- CTEs: consolidam pedidos e produtos vendidos dentro da Stored Procedure.

O endpoint `api/dashboard.php` chama a Stored Procedure e normaliza os conjuntos de resultados para JSON. O TypeScript usa esses dados para atualizar a interface sem recarregar toda a página.

## TypeScript

Os fontes ficam em `typescript/src`.

| Arquivo | Uso |
|---|---|
| `api.ts` | Funções compartilhadas para chamadas HTTP e mensagens. |
| `login.ts` | Login do cliente. |
| `admin-login.ts` | Login exclusivo do administrador. |
| `cadastro.ts` | Cadastro de clientes. |
| `admin-produtos.ts` | Interações do gerenciamento de produtos. |
| `dashboard.ts` | Filtros, métricas, listas e paginação da dashboard. |

Para instalar as dependências:

```powershell
cd C:\xampp\htdocs\Droz-Robotica\typescript
npm install
```

Para verificar os tipos sem gerar arquivos:

```powershell
npm run check
```

Para compilar os arquivos em `public/assets/js`:

```powershell
npm run build
```

Para recompilar automaticamente durante o desenvolvimento:

```powershell
npm run watch
```

Não execute vários arquivos `.ts` separadamente. O `tsconfig.json` já informa ao TypeScript quais arquivos-fonte devem ser compilados; use os comandos acima.

## Testes e validações

### Validar todos os arquivos PHP

No PowerShell, a partir da raiz do projeto:

```powershell
$php = 'C:\xampp\php\php.exe'
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    & $php -l $_.FullName
}
```

Cada arquivo deve retornar `No syntax errors detected`.

### Validar TypeScript

```powershell
cd typescript
npm install
npm run check
npm run build
```

### Fluxos recomendados para teste manual

1. Crie uma conta de cliente.
2. Entre como administrador.
3. Crie uma categoria.
4. Cadastre um produto comprável com estoque.
5. Cadastre ou edite outro produto como somente orçamento.
6. Entre como cliente e confirme que as duas ações são diferentes no catálogo.
7. Adicione o produto comprável ao carrinho e finalize o pedido.
8. Abra Minha conta e confira o histórico.
9. Atualize o telefone e uma foto de perfil.
10. Entre como admin e confira pedido, cliente, itens, valor e status.
11. Envie um orçamento e confirme o produto vinculado na administração.
12. Teste os filtros da dashboard.

## Problemas comuns

### `Erro ao conectar ao banco de dados`

- confirme que o MySQL está iniciado no XAMPP;
- confira host, porta, usuário, senha e banco em `config/config.php`;
- confirme que `droz_robotica` foi importado;
- verifique se outra instalação do MySQL está usando a porta `3306`.

### `MySQL shutdown unexpectedly`

- confira se a porta `3306` está ocupada por outro serviço MySQL;
- abra os logs do MySQL pelo XAMPP;
- execute o XAMPP como administrador quando necessário;
- faça backup da pasta de dados antes de qualquer reparo;
- não apague arquivos do banco sem identificar a causa.

### Página abre, mas CSS, imagens ou links retornam 404

- confirme que o `DocumentRoot` aponta para `Droz-Robotica/public`;
- confira o domínio `drozrobotica.local` no arquivo `hosts`;
- confira a porta `8080` no Apache e em `BASE_URL`;
- reinicie o Apache após alterar o VirtualHost.

### Login administrativo não funciona

- confirme que a senha no banco está armazenada como hash;
- gere um novo hash com o comando apresentado neste README;
- atualize o usuário `admin@drozrobotica.com`;
- confirme que `tipo = 'admin'` e `ativo = 1`.

### Dashboard não mostra dados

- confirme que existem pedidos dentro do período selecionado;
- pedidos cancelados não entram no faturamento;
- em ambiente de demonstração, execute `database/seed_dashboard.sql` uma única vez;
- em banco antigo, execute `database/atualizacao_rubrica.sql`;
- confira se a procedure `sp_dashboard_indicadores` existe.

### Produto não aparece no catálogo

- confirme que o produto está ativo;
- confirme que possui uma categoria válida;
- limpe os filtros e a busca do catálogo.

### Produto aparece, mas não pode ser comprado

Isso é esperado quando `permite_pedido` está desativado. Nesse caso, o sistema direciona o usuário para orçamento.

### Foto do perfil não é salva

- use JPG, PNG ou WEBP com até 2 MB;
- confirme permissão de escrita em `public/uploads/perfis`;
- confirme que `database/atualizacao_perfil.sql` foi aplicado em bancos antigos.

## Publicação

Antes de colocar o projeto em um servidor online:

- aponte o domínio para a pasta `public`, nunca para a raiz inteira do projeto;
- altere `BASE_URL` para o domínio HTTPS real;
- crie um usuário exclusivo para o banco e não use `root`;
- não publique credenciais em `config/config.php`;
- prefira variáveis de ambiente para dados sensíveis;
- troque a senha administrativa de demonstração;
- habilite HTTPS;
- configure cookies de sessão com `Secure`, `HttpOnly` e `SameSite`;
- garanta permissão de escrita apenas nas pastas de upload;
- mantenha execução de scripts bloqueada nas pastas de upload;
- faça backup do banco e dos uploads;
- não execute `droz_robotica.sql` sobre o banco de produção;
- remova ou não execute `seed_dashboard.sql` em produção.

## Segurança implementada

- senhas protegidas com algoritmo gerenciado pelo PHP;
- consultas parametrizadas com PDO;
- regeneração do ID da sessão após login;
- rotas administrativas protegidas por perfil;
- token CSRF em formulários sensíveis;
- validação de URL de redirecionamento interno;
- escape de saída HTML;
- histórico de pedidos vinculado à sessão do usuário;
- validação de formato, MIME, tamanho e nome aleatório nas fotos;
- bloqueio de execução de scripts na pasta de fotos;
- regras para preservar histórico e impedir remoção do último admin.

## Observação acadêmica

Este projeto demonstra integração entre frontend, backend e banco relacional, incluindo CRUDs, autenticação, autorização, relacionamentos, transações, API JSON, TypeScript, filtros, paginação, função SQL, view, Stored Procedure, triggers, índices e CTEs.
