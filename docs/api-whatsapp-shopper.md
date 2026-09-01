# API JSON para o Shopper do Oferto no WhatsApp

Esta API foi pensada para o bot consultar as ofertas direto do MySQL do `cupons.oferto.digital`, sem depender de um arquivo JSON estático.

## Precisa atualizar todo dia?

Não manualmente. Como os endpoints leem o MySQL em tempo real, o JSON muda sozinho quando a rotina de sincronização atualiza o banco. Ou seja:

- Se a Lomadee, Awin, HasOffers ou outra integração atualizar o MySQL, a API já passa a devolver os dados novos.
- Se uma oferta vencer, ela deixa de aparecer porque os endpoints usam apenas ofertas ativas dentro da validade.
- Se no futuro houver cache, o ideal é usar cache curto, de 5 a 15 minutos, não uma atualização diária manual.

## Endpoints para o bot

### 1. Categorias

```http
GET https://cupons.oferto.digital/api/categories.php
```

Uso no bot: quando a pessoa pedir ajuda geral, o bot pode listar categorias com ofertas ativas, como alimentação, beleza, casa, moda ou serviços.

Campos principais:

- `id`: slug da categoria.
- `name`: nome exibido ao usuário.
- `offer_count`: total de ofertas ativas.
- `featured_count`: quantas estão em destaque.
- `expires_today_count`: quantas vencem hoje.
- `expires_soon_count`: quantas vencem em até 3 dias.
- `niches`: nichos existentes dentro daquela categoria, com contagem e `api_url`.
- `product_tags`: tags de produto encontradas nas ofertas daquela categoria.
- `url`: link da categoria no site.
- `api_url`: link para buscar ofertas dessa categoria.

O endpoint também devolve uma lista geral em `niches`, útil quando o bot quiser começar pelo nicho em vez da categoria visual do site.

### 2. Ofertas

```http
GET https://cupons.oferto.digital/api/offers.php
```

Filtros opcionais:

- `category`: slug ou nome da categoria. Exemplo: `alimentacao-e-bebidas`.
- `niche` ou `nicho`: nicho principal. Exemplo: `saude_farmacia`.
- `tag`: busca em tags de produto e tags públicas. Exemplo: `pizza`.
- `store`: busca parcial pelo nome da loja.
- `q`: busca textual em loja, título, descrição, regras e tags.
- `featured=1`: lista apenas ofertas em destaque.
- `limit`: quantidade de ofertas, de 1 a 100. Padrão: 50.

Exemplos:

```http
GET https://cupons.oferto.digital/api/offers.php?category=alimentacao-e-bebidas&limit=10
GET https://cupons.oferto.digital/api/offers.php?niche=saude_farmacia
GET https://cupons.oferto.digital/api/offers.php?tag=pizza
GET https://cupons.oferto.digital/api/offers.php?q=pizza
GET https://cupons.oferto.digital/api/offers.php?store=China%20in%20Box
```

Campos seguros para o bot:

- `id`
- `store`
- `category`
- `category_slug`
- `nicho_principal`
- `title`
- `description`
- `rules`
- `offer_type`
- `offer_type_label`
- `redemption_type`
- `redemption_type_label`
- `cta_label`
- `has_public_code`
- `code`
- `mechanic_label`
- `mechanic_value`
- `banner_url`
- `logo_url`
- `starts_at`
- `ends_at`
- `days_until_end`
- `validity_label`
- `featured`
- `sponsored`
- `members_only`
- `tags`
- `tags_produto`
- `offer_url`
- `rescue_url`
- `share_text`

O campo recomendado para enviar no WhatsApp é `offer_url`, porque ele leva para a pré-página da oferta no Oferto. Assim o usuário consegue ler a descrição, copiar o cupom quando existir e depois seguir para a loja.

O campo `rescue_url` é a saída rastreada para a loja. Ele deve ser usado quando o usuário já decidiu abrir a oferta.

### 3. Esquema tecnico

```http
GET https://cupons.oferto.digital/api/schema.php
```

Uso: mostra para o time técnico quais endpoints existem, quais campos saem na API e quais tabelas existem no MySQL.

## Tabelas do MySQL

O banco tem 9 tabelas principais:

- `coupons`: tabela central de ofertas, cupons, campanhas e sorteios.
- `mapa_loja_nicho`: dicionário auxiliar que liga nome da loja a nicho e tags de produto.
- `coupon_clicks`: cliques e eventos de saída.
- `affiliate_conversions`: conversões recebidas das redes de afiliados.
- `integration_settings`: configurações das integrações.
- `integration_watchlist`: campanhas/ofertas monitoradas.
- `integration_brand_watchlist`: marcas monitoradas.
- `admin_notifications`: alertas internos.
- `admins`: usuários administrativos.

Para o bot, a tabela principal é `coupons`, mas o consumo deve acontecer via `/api/offers.php`, porque essa rota já limpa os campos internos.

## Relação entre `coupons` e `mapa_loja_nicho`

`mapa_loja_nicho` não substitui `coupons`; ela funciona como uma tabela auxiliar de consulta.

Fluxo prático:

1. `coupons` tem duas colunas finais: `nicho_principal` e `tags_produto`.
2. `mapa_loja_nicho` guarda um dicionario por loja: `nome_loja`, `nicho_principal` e `tags_produto`.
3. Quando uma oferta e salva ou atualizada, o sistema procura o `store` da oferta em `mapa_loja_nicho`.
4. Se encontrar, copia o nicho e as tags para a linha da oferta em `coupons`.
5. Se não encontrar, a oferta continua salva normalmente, só com esses campos vazios.

## Como atualizar nichos e tags por CSV

Quando o time receber um CSV classificado, entre no CRM em `admin/import-classifications.php` e envie o arquivo. O formato mínimo esperado é:

```csv
id,loja,categoria_atual,nicho_principal,titulo
251,BioVittare Farmácia de Manipulação,Saúde e Beleza,saude_farmacia,8% OFF para montar seu pedido
```

A coluna `tags_produto` é opcional. Quando ela não vem na planilha, o CRM gera tags automaticamente usando loja, categoria, nicho e palavras úteis do título. Isso resolve o caso em que o JSON ainda aparece com `nicho_principal` e `tags_produto` vazios: primeiro o CSV atualiza o MySQL, depois `/api/offers.php` passa a devolver os campos preenchidos.

Também existe um script para rodar direto no servidor:

```bash
php scripts/import-offer-classifications.php /caminho/para/ofertas_classificadas.csv
```

## Fluxo recomendado no WhatsApp

1. Usuário pede algo amplo: "quero economizar no mercado" ou "tem cupom de pizza?"
2. Bot chama `/api/offers.php?q=mercado` ou `/api/offers.php?q=pizza`.
3. Bot mostra 3 a 5 opções com `store`, `title`, `validity_label` e, quando houver, `code`.
4. Bot envia o `offer_url` da pré-página para manter o usuário dentro do Oferto.
5. Depois que o usuário escolher, o bot pode usar o `rescue_url` para mandar para a loja.

## Campos que não devem aparecer para o usuário

Mesmo existindo no banco, estes campos nao devem ir para a interface do bot:

- `tracking_url`
- `target_url`
- `partner_network`
- `payout`
- `campaign_cap`
- `external_id`
- `pixel_event`
- `raw_json`
- qualquer campo de usuario administrativo
