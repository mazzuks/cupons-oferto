# Instalacao no cPanel/Turbocloud

Este projeto tem uma versao PHP/MySQL simples para hospedar cupons, banners, links, textos e validade.

## 1. Criar o banco

No cPanel:

1. Abra **MySQL Databases**.
2. Crie um banco, por exemplo `usuario_cupons`.
3. Crie um usuario MySQL.
4. Dê permissao total desse usuario para o banco.
5. As tabelas sao criadas automaticamente no primeiro acesso depois que `includes/config.php` estiver configurado. O arquivo `install.sql` fica como apoio manual, se precisar.

## 2. Configurar o site

1. Copie `includes/config.example.php`.
2. Renomeie a copia para `includes/config.php`.
3. Preencha os dados do banco:

```php
'host' => 'localhost',
'name' => 'NOME_DO_BANCO',
'user' => 'USUARIO_DO_BANCO',
'pass' => 'SENHA_DO_BANCO',
```

Na maioria dos cPanels, o host e `localhost`.

## 3. Subir os arquivos

No cPanel, use o mesmo processo do projeto de campanhas:

1. Abra **Git Version Control**.
2. Clone `https://github.com/mazzuks/cupons-oferto.git`.
3. Use uma pasta fora do site oficial, por exemplo `/home/oferto/cupons-oferto`.
4. Em **Domains**, crie ou gerencie estes subdominios:
   - `cupons.oferto.digital` apontando para `/home/oferto/cupons-oferto`;
   - `crm.oferto.digital` apontando para `/home/oferto/cupons-oferto`.

Quando o acesso vier por `crm.oferto.digital`, o `index.php` redireciona automaticamente para `/admin/`. Assim o site publico e o painel usam a mesma base, o mesmo banco e a mesma pasta de uploads.

Pastas importantes:

- `admin/`: painel administrativo.
- `assets/`: banners fixos do projeto.
- `includes/`: configuracao e funcoes PHP.
- `uploads/cupons/`: banners enviados pelo admin.

Garanta que `uploads/cupons/` tenha permissao de escrita pelo PHP. Em muitos cPanels, `755` funciona; em alguns, pode precisar de `775`.

## 4. Criar o primeiro admin

Acesse:

```text
https://seu-dominio.com/admin/setup.php
```

Crie o primeiro usuario administrador. Depois disso, a pagina de setup deixa de criar novos usuarios e redireciona para o login.

## 5. Cadastrar cupons

Acesse:

```text
https://seu-dominio.com/admin/
```

O painel permite:

- criar cupom;
- editar cupom;
- excluir cupom;
- subir banner;
- usar URL de banner externo;
- definir categoria;
- trocar link de destino;
- alterar textos;
- colocar data de inicio e fim;
- usar `ativo`, `rascunho` ou `pausado`;
- marcar destaque.

Somente cupons `ativo`, com `inicio <= hoje` e `fim >= hoje`, aparecem no site.

## 6. Integracoes de afiliados

No CRM, acesse `APIs`. Cada parceiro tem sua propria tela:

- `Lomadee`: chave, busca de marcas, filtros, selecao de campanhas e importacao.
- `Awin`: token, publisher, filtros, busca de ofertas e sincronizacao.

As chaves nao devem ser salvas no GitHub. Elas ficam no banco do servidor ou, se preferir, no `includes/config.php` fora do repositorio.

Quando uma oferta e importada por uma API, ela entra na lista de monitoramento. A sincronizacao diaria confere se ela continua aparecendo no feed do parceiro.

## 7. Cron diaria

No cPanel, abra **Cron Jobs** e crie uma tarefa diaria a meia-noite:

```bash
0 0 * * * /usr/local/bin/php /home/USUARIO/cupons-oferto/scripts/sync-integrations.php
```

Troque `/home/USUARIO/cupons-oferto` pela pasta real do clone no servidor.

Essa cron:

- atualiza campanhas monitoradas;
- marca campanhas que sumiram do feed;
- cria notificacoes no CRM;
- registra erros de API em `Notificacoes`.

## Observacao sobre categorias

Use categorias consistentes. Exemplo: Ruffles, snacks, bebidas, restaurantes e delivery entram em `Alimentação e Bebidas`.
