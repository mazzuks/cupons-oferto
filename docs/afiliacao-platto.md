# Módulo de afiliação: Platto para Oferto

## O que foi analisado no Platto

O módulo B2B do Platto trata afiliação como um sistema próprio, não como lista de cupons. A estrutura principal tem:

- `affiliates`: cadastro de afiliados/parceiros, status, dados de contato e pagamento.
- `campaigns`: campanhas com URL de destino, modo de tracking, redirect, cookie TTL, UTM gate, postback secret e domínios permitidos.
- `clicks`: registro de clique por campanha, afiliado, TID, UTM, referer, user agent e IP.
- `conversions`: registro de conversão por campanha, afiliado, TID, pedido, valor, moeda e assinatura.
- `affiliate_transactions`: carteira com ganhos, saques, bônus, ajustes e status financeiro.

As telas do Platto que serviram de base foram:

- `AffiliatesManager.tsx`: lista afiliados, filtra, cria afiliado, gera smartlink e mostra métricas.
- `AffiliateWalletPanel.tsx`: calcula saldo disponível, pendente, aprovado, saques e histórico.
- `AvailableCampaigns.tsx`: separa campanhas disponíveis e permite candidatura/seleção.
- `tracking-redirect`: registra clique, gera TID, preserva UTM e redireciona.
- `tracking-conversion`: valida HMAC, evita duplicidade e credita comissão.

## Adaptação correta no Oferto

O Oferto tem duas camadas diferentes:

- `coupons`: vitrine pública de cupons, descontos, sorteios e ofertas para o leitor.
- `affiliate_campaigns`: campanhas operacionais de afiliação, com tracking, payout, conversões e status próprio.

Um cupom pode virar campanha afiliada, mas isso deve ser uma escolha. Por isso existe a tela `Afiliação > Selecionar cupons`, que copia o item selecionado para `affiliate_campaigns` e deixa a vitrine pública intacta.

## Subguias criadas no CRM

- `Campanhas`: lê somente `affiliate_campaigns`, sem consultar a vitrine pública como fonte operacional.
- `Parceiros`: lê `affiliate_partners` e mostra cliques, conversões, ganhos e saques por afiliado.
- `Tracking`: mostra modo de tracking, redirect, cookie TTL, postback secret e o padrão de smartlink por campanha.
- `Carteira`: lê `affiliate_transactions` e separa ganhos, pendências, aprovações e saques.
- `Selecionar cupons`: ponte manual para copiar uma oferta da vitrine para `affiliate_campaigns`.

## Próximos blocos que ainda faltam

- Cadastro completo de parceiros afiliados no CRM.
- Smartlink por campanha e parceiro.
- Endpoint de redirect afiliado separado do `go.php` de cupons.
- Endpoint de postback/conversão com HMAC.
- Carteira do parceiro com ganhos, saques e ajustes.
- Importação das redes cair primeiro em `affiliate_campaigns` e só publicar na vitrine quando a campanha for aprovada.
- Tela de detalhe de campanha com postback secret, snippet/pixel, allowed domains e modo de redirect.
