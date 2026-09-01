<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

api_json_response([
    'ok' => true,
    'generated_at' => api_now(),
    'purpose' => 'Esquema para integrar o bot Shopper do Oferto no WhatsApp usando dados vivos do MySQL.',
    'important_note' => 'O bot deve consumir os endpoints públicos. Campos internos de afiliação, tracking bruto e comissão não devem ser exibidos ao usuário final.',
    'endpoints' => [
        [
            'method' => 'GET',
            'url' => OFERTO_API_BASE_URL . 'api/categories.php',
            'description' => 'Lista categorias com quantidade de ofertas ativas.',
        ],
        [
            'method' => 'GET',
            'url' => OFERTO_API_BASE_URL . 'api/offers.php',
            'query_params' => [
                'category' => 'Slug ou nome da categoria. Exemplo: alimentacao-e-bebidas.',
                'niche' => 'Nicho principal da oferta. Também aceita o alias nicho. Exemplo: saude_farmacia.',
                'tag' => 'Busca em tags de produto e tags públicas. Exemplo: pizza.',
                'store' => 'Busca parcial pelo nome da loja.',
                'q' => 'Busca textual em loja, titulo, descricao, regras e tags.',
                'featured' => 'Use 1 para listar apenas destaques.',
                'limit' => 'Quantidade de ofertas, de 1 a 100. Padrao: 50.',
            ],
            'description' => 'Lista ofertas ativas, ja no formato seguro para o bot.',
        ],
        [
            'method' => 'GET',
            'url' => OFERTO_API_BASE_URL . 'api/schema.php',
            'description' => 'Mostra o esquema de integracao, tabelas principais e campos.',
        ],
    ],
    'public_offer_fields' => [
        'id' => 'Identificador da oferta.',
        'store' => 'Nome da loja ou marca.',
        'category' => 'Categoria exibida ao usuario.',
        'category_slug' => 'Categoria em formato amigavel para filtro por API.',
        'nicho_principal' => 'Nicho principal herdado do mapa de loja quando existir.',
        'title' => 'Chamada principal da oferta.',
        'description' => 'Descricao curta para o bot contextualizar a recomendacao.',
        'rules' => 'Regras e observacoes publicas.',
        'offer_type' => 'Tipo interno da oferta: cupom, sorteio, cashback, oferta_direta etc.',
        'offer_type_label' => 'Tipo da oferta em texto amigavel.',
        'redemption_type' => 'Forma de resgate: texto, texto_redirect ou redirect.',
        'redemption_type_label' => 'Forma de resgate em texto amigavel.',
        'cta_label' => 'Texto recomendado para botao/chamada.',
        'has_public_code' => 'Indica se existe cupom publico para copiar.',
        'code' => 'Codigo do cupom apenas quando pode ser exibido publicamente.',
        'mechanic_label' => 'Rotulo da mecanica: Codigo, Oferta, Cashback etc.',
        'mechanic_value' => 'Valor publico da mecanica.',
        'banner_url' => 'Imagem da oferta.',
        'logo_url' => 'Logo ou favicon da marca.',
        'starts_at' => 'Data inicial.',
        'ends_at' => 'Data final.',
        'days_until_end' => 'Dias restantes.',
        'validity_label' => 'Texto pronto de validade.',
        'featured' => 'Se a oferta esta marcada como destaque.',
        'sponsored' => 'Se a oferta tem prioridade comercial.',
        'members_only' => 'Se depende de acesso/cadastro.',
        'tags' => 'Tags publicas para busca e contexto.',
        'tags_produto' => 'Tags de produto herdadas do mapa de loja quando existir.',
        'offer_url' => 'Pre-pagina do Oferto. Recomendado para compartilhar no WhatsApp.',
        'rescue_url' => 'URL de saida rastreada pelo Oferto. Usar apenas apos o usuario decidir abrir a loja.',
        'share_text' => 'Texto pronto para compartilhamento.',
    ],
    'database_tables' => [
        'coupons' => [
            'usage' => 'Tabela central de ofertas, cupons, campanhas e sorteios.',
            'fields' => [
                'id', 'category', 'store', 'title', 'description', 'code', 'target_url', 'banner_url', 'logo_url',
                'starts_at', 'ends_at', 'status', 'featured', 'rules', 'redemption_type', 'offer_type', 'cta_label',
                'tracking_url', 'partner_network', 'payout', 'campaign_cap', 'sponsored', 'priority', 'tags',
                'nicho_principal', 'tags_produto', 'requirements', 'pixel_event', 'external_id', 'members_only', 'created_at', 'updated_at',
            ],
            'bot_rule' => 'Consumir via api/offers.php para evitar exposicao de campos internos.',
        ],
        'mapa_loja_nicho' => [
            'usage' => 'Dicionario auxiliar que liga o nome da loja ao nicho e as tags de produto.',
            'fields' => ['id', 'nome_loja', 'nicho_principal', 'tags_produto', 'status', 'created_at', 'updated_at'],
            'bot_rule' => 'Serve para alimentar os campos finais em coupons. O bot pode ignorar essa tabela e consumir o resultado por api/offers.php.',
        ],
        'coupon_clicks' => [
            'usage' => 'Registra cliques e eventos de saida.',
            'fields' => ['id', 'coupon_id', 'click_ref', 'event_type', 'referer', 'user_agent', 'ip_hash', 'created_at'],
            'bot_rule' => 'O bot nao precisa gravar aqui diretamente; usar offer_url ou rescue_url.',
        ],
        'affiliate_conversions' => [
            'usage' => 'Armazena conversoes recebidas das redes de afiliados.',
            'fields' => ['id', 'partner', 'external_conversion_id', 'coupon_id', 'external_id', 'click_ref', 'store', 'status', 'sale_amount', 'commission_amount', 'currency', 'conversion_at', 'raw_json', 'created_at', 'updated_at'],
            'bot_rule' => 'Tabela administrativa, nao exibir ao usuario final.',
        ],
        'integration_settings' => [
            'usage' => 'Configuracoes das integracoes e rotinas de sincronizacao.',
            'fields' => ['setting_key', 'setting_value', 'updated_at'],
            'bot_rule' => 'Tabela administrativa.',
        ],
        'integration_watchlist' => [
            'usage' => 'Monitora campanhas/ofertas selecionadas nas redes.',
            'fields' => ['id', 'partner', 'external_id', 'source_id', 'brand_id', 'store', 'title', 'status', 'last_seen_at', 'missing_since', 'created_at', 'updated_at'],
            'bot_rule' => 'Tabela administrativa.',
        ],
        'integration_brand_watchlist' => [
            'usage' => 'Monitora marcas selecionadas nas redes.',
            'fields' => ['id', 'partner', 'brand_id', 'brand_name', 'segment', 'status', 'last_seen_at', 'created_at', 'updated_at'],
            'bot_rule' => 'Tabela administrativa.',
        ],
        'admin_notifications' => [
            'usage' => 'Alertas internos do painel.',
            'fields' => ['id', 'type', 'title', 'body', 'partner', 'external_id', 'read_at', 'created_at'],
            'bot_rule' => 'Tabela administrativa.',
        ],
        'admins' => [
            'usage' => 'Usuarios administrativos.',
            'fields' => ['id', 'name', 'email', 'password_hash', 'created_at'],
            'bot_rule' => 'Nunca expor ao bot.',
        ],
    ],
]);
