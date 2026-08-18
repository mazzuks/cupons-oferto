<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_admin(): void
{
    if (!current_admin()) {
        redirect('login.php');
    }
}

function login_admin(string $email, string $password): bool
{
    $pdo = db();
    if (!$pdo) {
        return false;
    }

    $statement = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $statement->execute([$email]);
    $admin = $statement->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id' => $admin['id'],
        'email' => $admin['email'],
        'name' => $admin['name'],
    ];

    return true;
}

function logout_admin(): void
{
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}

function admin_count(): int
{
    $pdo = db();
    if (!$pdo) {
        return 0;
    }

    return (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
}

function create_admin(string $name, string $email, string $password): void
{
    $pdo = db();
    if (!$pdo) {
        throw new RuntimeException('Banco de dados indisponivel.');
    }

    $statement = $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)');
    $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
}
