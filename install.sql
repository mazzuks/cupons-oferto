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
  starts_at DATE NOT NULL,
  ends_at DATE NOT NULL,
  status ENUM('ativo', 'rascunho', 'pausado') NOT NULL DEFAULT 'rascunho',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  rules TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY coupons_public_idx (status, starts_at, ends_at, featured),
  KEY coupons_category_idx (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO coupons
  (category, store, title, description, code, target_url, banner_url, starts_at, ends_at, status, featured, rules)
VALUES
  ('Alimentação e Bebidas', 'Pizza Hut', 'Pizza grande com desconto para dividir', 'Cupom para economizar no pedido de pizza do fim de semana.', 'PIZZAOFERTA', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/1-1.jpg', '2026-08-10', '2026-08-22', 'ativo', 1, 'Confira disponibilidade, lojas participantes e pedido mínimo antes de finalizar.'),
  ('Alimentação e Bebidas', 'Ruffles', 'Salgadinho para o lanche com cupom', 'Oferta para economizar em snacks, bebidas e itens de conveniência.', 'RUFFLES10', 'https://oferto.digital/', 'assets/ruffles-coupon.svg', '2026-08-18', '2026-08-24', 'ativo', 0, 'Produto alimentício: classificar sempre em Alimentação e Bebidas.'),
  ('Compras', 'Mercado Online', 'Economia na compra do mês', 'Desconto para abastecer a casa sem estourar o orçamento.', 'CASA10', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/2-1.jpg', '2026-08-12', '2026-08-19', 'ativo', 1, 'Válido enquanto houver disponibilidade da campanha.'),
  ('Games', 'Gift Card Store', 'Créditos para jogar pagando menos', 'Cupom para comprar gift cards e renovar assinaturas de games.', 'GAMEPLAY', 'https://oferto.digital/', 'https://oferto.digital/wp-content/uploads/2024/08/3-1.jpg', '2026-08-14', '2026-08-27', 'ativo', 0, 'Pode variar conforme o valor do gift card escolhido.');
