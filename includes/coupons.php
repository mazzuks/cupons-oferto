<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function fallback_coupons(): array
{
    return [
        [
            'id' => 1,
            'category' => 'Alimentação e Bebidas',
            'store' => 'Pizza Hut',
            'title' => 'Pizza grande com desconto para dividir',
            'description' => 'Cupom para economizar no pedido de pizza do fim de semana.',
            'code' => 'PIZZAOFERTA',
            'target_url' => 'https://oferto.digital/',
            'banner_url' => 'https://oferto.digital/wp-content/uploads/2024/08/1-1.jpg',
            'starts_at' => '2026-08-10',
            'ends_at' => '2026-08-22',
            'status' => 'ativo',
            'featured' => 1,
            'rules' => 'Confira disponibilidade, lojas participantes e pedido mínimo antes de finalizar.',
        ],
        [
            'id' => 2,
            'category' => 'Alimentação e Bebidas',
            'store' => 'Ruffles',
            'title' => 'Salgadinho para o lanche com cupom',
            'description' => 'Oferta para economizar em snacks, bebidas e itens de conveniência.',
            'code' => 'RUFFLES10',
            'target_url' => 'https://oferto.digital/',
            'banner_url' => 'assets/ruffles-coupon.svg',
            'starts_at' => '2026-08-18',
            'ends_at' => '2026-08-24',
            'status' => 'ativo',
            'featured' => 0,
            'rules' => 'Produto alimentício: classificar sempre em Alimentação e Bebidas.',
        ],
    ];
}

function active_coupons(): array
{
    $pdo = db();
    if (!$pdo) {
        return fallback_coupons();
    }

    $sql = "SELECT * FROM coupons
            WHERE status = 'ativo'
              AND starts_at <= CURDATE()
              AND ends_at >= CURDATE()
            ORDER BY featured DESC, ends_at ASC, store ASC";

    return $pdo->query($sql)->fetchAll();
}

function all_coupons(): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }

    return $pdo->query('SELECT * FROM coupons ORDER BY created_at DESC, id DESC')->fetchAll();
}

function coupon_by_id(int $id): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM coupons WHERE id = ?');
    $statement->execute([$id]);
    $coupon = $statement->fetch();

    return $coupon ?: null;
}

function save_coupon(array $data, ?int $id = null): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $fields = [
        'category',
        'store',
        'title',
        'description',
        'code',
        'target_url',
        'banner_url',
        'starts_at',
        'ends_at',
        'status',
        'featured',
        'rules',
    ];

    if ($id) {
        $sets = implode(', ', array_map(fn ($field) => "$field = :$field", $fields));
        $statement = $pdo->prepare("UPDATE coupons SET $sets, updated_at = NOW() WHERE id = :id");
        $data['id'] = $id;
        $statement->execute($data);
        return;
    }

    $columns = implode(', ', $fields);
    $params = ':' . implode(', :', $fields);
    $statement = $pdo->prepare("INSERT INTO coupons ($columns) VALUES ($params)");
    $statement->execute($data);
}

function delete_coupon(int $id): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $statement = $pdo->prepare('DELETE FROM coupons WHERE id = ?');
    $statement->execute([$id]);
}

function days_until(string $date): int
{
    $today = new DateTimeImmutable('today');
    $end = new DateTimeImmutable($date);
    return (int) $today->diff($end)->format('%r%a');
}

function validity_label(string $date): string
{
    $days = days_until($date);
    if ($days < 0) {
        return 'Encerrado';
    }
    if ($days === 0) {
        return 'Vence hoje';
    }
    if ($days === 1) {
        return 'Vence amanhã';
    }
    return "Vence em {$days} dias";
}
