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

## 6. Afiliação e integrações

No CRM, acesse `Afiliação`. A vitrine pública de cupons continua na tabela `coupons`; o módulo de afiliação usa tabelas próprias:

- `affiliate_campaigns`: campanhas afiliadas selecionadas, publicadas ou pausadas.
- `affiliate_partners`: parceiros/afiliados que podem receber smartlinks no futuro.
- `affiliate_clicks`: cliques de tracking do módulo afiliado.
- `affiliate_campaign_conversions`: conversões atribuídas a campanhas afiliadas.
- `affiliate_transactions`: carteira e movimentações dos parceiros.

Quando um cupom/desconto da vitrine fizer sentido para afiliação, use `Afiliação > Selecionar cupons`. Isso copia o item para `affiliate_campaigns`, sem misturar a tabela pública de cupons com a tabela operacional de afiliação.

As subguias de afiliação ficam separadas por função:

- `Campanhas`: campanhas afiliadas importadas, selecionadas, publicadas ou pausadas.
- `Parceiros`: cadastro e desempenho dos afiliados/parceiros.
- `Tracking`: smartlinks, modo de redirect, cookie TTL e postback secret.
- `Carteira`: ganhos, aprovações, pendências e saques.
- `Selecionar cupons`: ponte manual entre vitrine pública e módulo afiliado.

Em `Afiliação > Redes`, cada parceiro tem sua própria tela:

- `Lomadee`: chave, busca de marcas, filtros, seleção de campanhas e importação.
- `Awin`: token, publisher, filtros, busca de ofertas e sincronização.

As chaves não devem ser salvas no GitHub. Elas ficam no banco do servidor ou, se preferir, no `includes/config.php` fora do repositório.

Quando uma marca é salva na tela do parceiro, ela entra na lista de monitoramento. A sincronização diária confere tudo que aparece daquela marca no feed do parceiro.

Quando uma oferta é importada por uma API, ela também entra na lista de campanhas monitoradas. Assim o CRM acompanha duas coisas:

- marcas que queremos garimpar sempre;
- campanhas já publicadas ou importadas.

## 7. Cron de sincronizacao

No cPanel, abra **Cron Jobs** e mantenha uma tarefa para atualizar as ofertas periodicamente.
No servidor atual, ela esta configurada para rodar a cada 2 horas, sempre no minuto 17:

```bash
17 */2 * * * cd /home/oferto/cupons-oferto && /usr/local/bin/php scripts/sync-integrations.php >> /home/oferto/cron-sync-integrations.log 2>&1
```

Se mudar de servidor, troque `/home/oferto/cupons-oferto` pela pasta real do clone e ajuste o caminho do arquivo de log.

Essa cron:

- atualiza campanhas monitoradas;
- avisa quando aparece campanha nova em marca monitorada;
- marca campanhas que sumiram do feed;
- cria notificacoes no CRM;
- registra erros de API em `Notificacoes`;
- grava um resumo em `Admin > Logs` com o evento `Cron de integracoes executada`.

Para confirmar se a cron rodou, abra `Admin > Logs` e procure o evento mais recente `integration_cron`.
Ele mostra quantas ofertas cada parceiro leu, quantas foram atualizadas, quantas entraram como novas e quantas ficaram ausentes.
Tambem da para conferir a saida tecnica em `/home/oferto/cron-sync-integrations.log`.

## Observacao sobre categorias

Use categorias consistentes. Exemplo: Ruffles, snacks, bebidas, restaurantes e delivery entram em `Alimentação e Bebidas`.
