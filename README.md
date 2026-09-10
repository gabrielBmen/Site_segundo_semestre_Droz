# DROZ Robótica — catálogo, pedidos e administração

Aplicação web em PHP, MariaDB/MySQL, Bootstrap 5 e TypeScript. O sistema separa claramente o acesso de clientes e administradores e oferece catálogo, orçamento, carrinho, pedidos e painel de análise.

## Recursos implementados

- Autenticação com senha protegida por `password_hash` e controle de sessão por perfil.
- Catálogo de produtos ativos, organizados por categorias.
- Produto configurável como **pedido online** ou **somente orçamento**.
- Carrinho e finalização de pedido somente para produtos ativos, compráveis e com estoque.
- Baixa de estoque e gravação de pedido/itens dentro de uma transação.
- Formulário de orçamento gravado na tabela `contatos`, com produto opcional.
- Administração de produtos, categorias, usuários, pedidos e orçamentos.
- Registro administrativo de vendas negociadas, com preço por venda, baixa de estoque e integração automática aos indicadores.
- Três CRUDs completos: produtos, categorias e usuários.
- Regras de exclusão para preservar pedidos e relacionamentos.
- Dashboard com período, datas específicas, busca, status, paginação, métricas e ranking.
- Operações administrativas protegidas com token CSRF.
- Área privada "Minha conta" com foto opcional, telefone editável, data de criação, contadores e histórico detalhado dos próprios pedidos.
- Menu lateral de conta; ações pessoais, carrinho, orçamento e dashboard administrativa não ficam expostos na navegação principal.

## Banco de dados e critérios técnicos

O banco completo cria as tabelas, chaves primárias/estrangeiras, relacionamento N:N entre pedidos e produtos e índices. O arquivo de rubrica cria a função, triggers, view e procedure:

- `fn_calcular_valor_item`: função usada para calcular o valor de cada item;
- `vw_pedido_itens_analiticos`: view que une pedidos, clientes, itens, produtos e categorias;
- `sp_dashboard_indicadores`: Stored Procedure com intervalo de datas, busca, filtro de status, limite e deslocamento;
- CTEs dentro da procedure para consolidar pedidos e ranquear produtos.

A API `api/dashboard.php` executa a procedure por `CALL` e devolve JSON normalizado. O TypeScript consome os dados assincronamente e usa `filter`, `map` e `reduce` para construir métricas e componentes visuais.

## Instalação nova no XAMPP

1. Inicie Apache e MySQL no XAMPP.
2. Importe `database/droz_robotica.sql` pelo phpMyAdmin, DBeaver ou cliente MySQL.
3. Em seguida, importe `database/rubrica.sql`.
4. Para carregar dados de demonstração, importe opcionalmente `database/seed_dashboard.sql`.
5. Coloque a pasta do projeto em `C:\xampp\htdocs`.
6. Confira as credenciais em `config/config.php`.
7. Configure o VirtualHost/hosts, se desejar usar `http://drozrobotica.local:8080`.
8. Acesse a aplicação pelo navegador.

> Atenção: `droz_robotica.sql` recria o banco do zero e apaga os dados anteriores. Use-o somente em uma instalação nova ou depois de gerar backup.

## Arquivos SQL mantidos no projeto

Para deixar a instalação e a apresentação mais simples, o repositório mantém somente três arquivos SQL:

1. `database/droz_robotica.sql` — cria o banco completo, com tabelas, relacionamentos, índices e dados iniciais;
2. `database/rubrica.sql` — cria a função, os triggers, a view analítica, as CTEs e a Stored Procedure exigidas pela rubrica;
3. `database/seed_dashboard.sql` — adiciona dados opcionais para demonstrar pedidos, métricas e o ranking da dashboard.

Em uma instalação nova, execute os arquivos nessa ordem. O terceiro arquivo é opcional. Para substituir um banco que já possui dados, faça um backup antes de importar novamente o banco completo.

## TypeScript

No terminal, entre na pasta `typescript` e execute:

```powershell
npm install
npm run check
npm run build
```

Os arquivos compilados são gravados em `public/assets/js`. `package.json` e `package-lock.json` devem permanecer versionados; somente `node_modules` fica fora do repositório.

## Estrutura principal

- `classes/`: entidades e validações de domínio.
- `models/`: acesso ao banco e transações.
- `controllers/`: validação e regras entre páginas e models.
- `api/`: respostas JSON e autenticação das chamadas assíncronas.
- `includes/`: template, autenticação, CSRF e funções compartilhadas.
- `public/`: páginas, administração e arquivos estáticos.
- `typescript/src/`: código TypeScript estritamente tipado.
- `database/`: banco completo, recursos exigidos pela rubrica e dados opcionais de demonstração.

## Fluxos para demonstrar na apresentação

1. Administrador cria uma categoria e cadastra um produto nela.
2. Produto comprável aparece com pedido online; produto não comprável aponta para orçamento.
3. Cliente faz login, adiciona um item ao carrinho e finaliza o pedido.
4. O admin abre Pedidos, confere cliente, itens, valor e data, e altera o status.
5. Cliente envia orçamento; o admin visualiza a solicitação e abre o WhatsApp.
6. Na dashboard, o admin muda período, busca/status e navega pelas páginas.
7. Ao tentar excluir uma categoria usada, produto com pedidos ou usuário com histórico, o sistema explica o bloqueio.
8. Cliente abre o menu da conta, atualiza o telefone e confere os itens e status de pedidos anteriores.

## Antes de publicar

- Troque as credenciais padrão e use um usuário de banco exclusivo da aplicação.
- Não publique senhas no repositório.
- Habilite HTTPS e configure cookies de sessão seguros no servidor.
- Faça backup do banco antes de rodar qualquer script de instalação.
