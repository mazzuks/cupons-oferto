CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY admins_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupons (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(120) NOT NULL,
  store VARCHAR(160) NOT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT NOT NULL,
  code VARCHAR(80) DEFAULT NULL,
  target_url VARCHAR(500) NOT NULL,
  banner_url VARCHAR(500) NOT NULL,
  logo_url VARCHAR(500) DEFAULT NULL,
  starts_at DATE NOT NULL,
  ends_at DATE NOT NULL,
  status ENUM('ativo', 'rascunho', 'pausado') NOT NULL DEFAULT 'rascunho',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  rules TEXT DEFAULT NULL,
  redemption_type VARCHAR(30) NOT NULL DEFAULT 'texto',
  offer_type VARCHAR(40) NOT NULL DEFAULT 'cupom',
  cta_label VARCHAR(80) DEFAULT NULL,
  tracking_url VARCHAR(500) DEFAULT NULL,
  partner_network VARCHAR(120) DEFAULT NULL,
  payout DECIMAL(10,2) DEFAULT NULL,
  campaign_cap INT UNSIGNED DEFAULT NULL,
  sponsored TINYINT(1) NOT NULL DEFAULT 0,
  priority INT NOT NULL DEFAULT 0,
  tags VARCHAR(500) DEFAULT NULL,
  nicho_principal VARCHAR(160) DEFAULT NULL,
  tags_produto VARCHAR(500) DEFAULT NULL,
  requirements VARCHAR(220) DEFAULT NULL,
  pixel_event VARCHAR(120) DEFAULT NULL,
  external_id VARCHAR(190) DEFAULT NULL,
  members_only TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY coupons_public_idx (status, starts_at, ends_at, featured),
  KEY coupons_category_idx (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mapa_loja_nicho (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_loja VARCHAR(160) NOT NULL,
  nicho_principal VARCHAR(160) NOT NULL,
  tags_produto VARCHAR(500) DEFAULT NULL,
  status ENUM('ativo', 'pausado') NOT NULL DEFAULT 'ativo',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY mapa_loja_nicho_nome_unique (nome_loja),
  KEY mapa_loja_nicho_status_idx (status),
  KEY mapa_loja_nicho_nicho_idx (nicho_principal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_clicks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  coupon_id INT UNSIGNED NOT NULL,
  click_ref VARCHAR(120) DEFAULT NULL,
  event_type VARCHAR(40) NOT NULL DEFAULT 'cta',
  referer VARCHAR(500) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  ip_hash CHAR(64) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY coupon_clicks_coupon_idx (coupon_id, created_at),
  KEY coupon_clicks_event_idx (event_type, created_at),
  KEY coupon_clicks_ref_idx (click_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integration_settings (
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integration_watchlist (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  partner VARCHAR(60) NOT NULL,
  external_id VARCHAR(190) NOT NULL,
  source_id VARCHAR(120) DEFAULT NULL,
  brand_id VARCHAR(120) DEFAULT NULL,
  store VARCHAR(160) NOT NULL,
  title VARCHAR(220) NOT NULL,
  status ENUM('monitorado', 'sumiu', 'pausado') NOT NULL DEFAULT 'monitorado',
  last_seen_at TIMESTAMP NULL DEFAULT NULL,
  missing_since TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY integration_watch_unique (partner, external_id),
  KEY integration_watch_status_idx (partner, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integration_brand_watchlist (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  partner VARCHAR(60) NOT NULL,
  brand_id VARCHAR(120) NOT NULL,
  brand_name VARCHAR(180) NOT NULL,
  segment VARCHAR(220) DEFAULT NULL,
  status ENUM('monitorado', 'pausado') NOT NULL DEFAULT 'monitorado',
  last_seen_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY integration_brand_watch_unique (partner, brand_id),
  KEY integration_brand_watch_status_idx (partner, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  type VARCHAR(60) NOT NULL,
  title VARCHAR(180) NOT NULL,
  body TEXT NOT NULL,
  partner VARCHAR(60) DEFAULT NULL,
  external_id VARCHAR(190) DEFAULT NULL,
  read_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY admin_notifications_read_idx (read_at, created_at),
  KEY admin_notifications_partner_idx (partner, external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS affiliate_conversions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  partner VARCHAR(60) NOT NULL,
  external_conversion_id VARCHAR(190) NOT NULL,
  coupon_id INT UNSIGNED DEFAULT NULL,
  external_id VARCHAR(190) DEFAULT NULL,
  click_ref VARCHAR(120) DEFAULT NULL,
  store VARCHAR(160) DEFAULT NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'pending',
  sale_amount DECIMAL(12,2) DEFAULT NULL,
  commission_amount DECIMAL(12,2) DEFAULT NULL,
  currency VARCHAR(10) DEFAULT NULL,
  conversion_at DATETIME DEFAULT NULL,
  raw_json MEDIUMTEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY affiliate_conversions_unique (partner, external_conversion_id),
  KEY affiliate_conversions_coupon_idx (coupon_id, conversion_at),
  KEY affiliate_conversions_partner_idx (partner, conversion_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS affiliate_partners (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  company_name VARCHAR(190) DEFAULT NULL,
  phone VARCHAR(80) DEFAULT NULL,
  website VARCHAR(255) DEFAULT NULL,
  status ENUM('ativo', 'pausado') NOT NULL DEFAULT 'ativo',
  payment_method VARCHAR(60) DEFAULT NULL,
  payment_reference VARCHAR(255) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY affiliate_partners_email_unique (email),
  KEY affiliate_partners_status_idx (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS affiliate_campaigns (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  source_coupon_id INT UNSIGNED DEFAULT NULL,
  published_coupon_id INT UNSIGNED DEFAULT NULL,
  network VARCHAR(80) NOT NULL DEFAULT 'manual',
  external_id VARCHAR(190) DEFAULT NULL,
  advertiser VARCHAR(190) NOT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT DEFAULT NULL,
  category VARCHAR(120) DEFAULT NULL,
  landing_url VARCHAR(500) NOT NULL,
  tracking_url VARCHAR(500) DEFAULT NULL,
  banner_url VARCHAR(500) DEFAULT NULL,
  logo_url VARCHAR(500) DEFAULT NULL,
  code VARCHAR(80) DEFAULT NULL,
  rules TEXT DEFAULT NULL,
  payout DECIMAL(12,2) DEFAULT NULL,
  payout_model VARCHAR(60) DEFAULT NULL,
  campaign_cap INT UNSIGNED DEFAULT NULL,
  starts_at DATE DEFAULT NULL,
  ends_at DATE DEFAULT NULL,
  status ENUM('disponivel', 'selecionada', 'publicada', 'pausada', 'encerrada') NOT NULL DEFAULT 'disponivel',
  tracking_mode ENUM('CLASSIC_PIXEL', 'JOURNEY_JS') NOT NULL DEFAULT 'CLASSIC_PIXEL',
  redirect_mode ENUM('FAST_302', 'HTML_BRIDGE') NOT NULL DEFAULT 'FAST_302',
  postback_secret VARCHAR(120) NOT NULL,
  cookie_ttl_days INT UNSIGNED NOT NULL DEFAULT 180,
  utm_source_gate VARCHAR(120) NOT NULL DEFAULT 'oferto',
  allowed_domains TEXT DEFAULT NULL,
  retargeting_config MEDIUMTEXT DEFAULT NULL,
  raw_json MEDIUMTEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY affiliate_campaigns_network_external_unique (network, external_id),
  KEY affiliate_campaigns_source_coupon_idx (source_coupon_id),
  KEY affiliate_campaigns_status_idx (status),
  KEY affiliate_campaigns_network_idx (network),
  KEY affiliate_campaigns_expiration_idx (ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS affiliate_clicks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  campaign_id INT UNSIGNED NOT NULL,
  affiliate_partner_id INT UNSIGNED DEFAULT NULL,
  tid VARCHAR(120) NOT NULL,
  click_ref VARCHAR(120) DEFAULT NULL,
  referer VARCHAR(500) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  ip_hash CHAR(64) DEFAULT NULL,
  utm_json TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY affiliate_clicks_campaign_idx (campaign_id, created_at),
  KEY affiliate_clicks_partner_idx (affiliate_partner_id, created_at),
  KEY affiliate_clicks_tid_idx (tid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS affiliate_campaign_conversions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  campaign_id INT UNSIGNED NOT NULL,
  affiliate_partner_id INT UNSIGNED DEFAULT NULL,
  tid VARCHAR(120) NOT NULL,
  order_id VARCHAR(190) DEFAULT NULL,
  value DECIMAL(12,2) DEFAULT NULL,
  commission_amount DECIMAL(12,2) DEFAULT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'BRL',
  status VARCHAR(60) NOT NULL DEFAULT 'pending',
  signature VARCHAR(190) DEFAULT NULL,
  raw_json MEDIUMTEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY affiliate_campaign_conversions_order_unique (campaign_id, order_id),
  KEY affiliate_campaign_conversions_campaign_idx (campaign_id, created_at),
  KEY affiliate_campaign_conversions_partner_idx (affiliate_partner_id, created_at),
  KEY affiliate_campaign_conversions_tid_idx (tid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS affiliate_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  affiliate_partner_id INT UNSIGNED NOT NULL,
  campaign_id INT UNSIGNED DEFAULT NULL,
  conversion_id BIGINT UNSIGNED DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  type ENUM('earning', 'withdrawal', 'bonus', 'adjustment') NOT NULL,
  status ENUM('pending', 'approved', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  description VARCHAR(255) DEFAULT NULL,
  metadata TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY affiliate_transactions_partner_idx (affiliate_partner_id, created_at),
  KEY affiliate_transactions_campaign_idx (campaign_id),
  KEY affiliate_transactions_conversion_idx (conversion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO coupons
  (category, store, title, description, code, target_url, banner_url, starts_at, ends_at, status, featured, rules, redemption_type, offer_type)
VALUES
  ('Alimentação e Bebidas', 'Pizza Hut', 'Pizza grande com desconto para dividir', 'Cupom para economizar no pedido de pizza do fim de semana.', 'PIZZAOFERTA', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/1-1.jpg', '2026-08-10', '2026-08-22', 'ativo', 1, 'Confira disponibilidade, lojas participantes e pedido mínimo antes de finalizar.', 'texto', 'cupom'),
  ('Alimentação e Bebidas', 'Ruffles', 'Salgadinho para o lanche com cupom', 'Oferta para economizar em snacks, bebidas e itens de conveniência.', 'RUFFLES10', 'https://oferto.digital/', 'assets/ruffles-coupon.svg', '2026-08-18', '2026-08-24', 'ativo', 0, 'Produto alimentício: classificar sempre em Alimentação e Bebidas.', 'texto', 'cupom'),
  ('Compras', 'Mercado Online', 'Economia na compra do mês', 'Desconto para abastecer a casa sem estourar o orçamento.', 'CASA10', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/2-1.jpg', '2026-08-12', '2026-08-19', 'ativo', 1, 'Válido enquanto houver disponibilidade da campanha.', 'texto', 'cupom'),
  ('Games', 'Gift Card Store', 'Créditos para jogar pagando menos', 'Cupom para comprar gift cards e renovar assinaturas de games.', 'GAMEPLAY', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/3-1.jpg', '2026-08-14', '2026-08-27', 'ativo', 0, 'Pode variar conforme o valor do gift card escolhido.', 'texto', 'cupom');
