<?php

declare(strict_types=1);

/**
 * Seeder CLI — Populates database with demo data.
 *
 * Usage:
 *   php bin/seed.php        Run all seeders
 *   php bin/seed.php users   Seed users only
 *   php bin/seed.php stats   Seed stats only
 */

require __DIR__ . '/../vendor/autoload.php';

use FrankenForge\Shared\Infrastructure\Database\Connection;

$dsn = $_ENV['DATABASE_URL']
    ?? $_SERVER['DATABASE_URL']
    ?? 'sqlite:' . __DIR__ . '/../storage/app.db';

$db = new Connection($dsn);
$seeder = $argv[1] ?? 'all';
$force = in_array('-f', $argv);

match ($seeder) {
    'all' => seedAll($db, $force),
    'users' => seedUsers($db, $force),
    'stats' => seedStats($db, $force),
    'invoices' => seedInvoices($db, $force),
    'toggles' => seedToggles($db, $force),
    default => die("Unknown seeder: {$seeder}. Use: all, users, stats, invoices, toggles\n"),
};

/**
 * @param Connection $db
 */
function seedAll(Connection $db, bool $force = false): void
{
    echo "Seeding all tables...\n";
    if ($force) {
        $db->execute('DELETE FROM toggles');
        $db->execute('DELETE FROM invoices');
        $db->execute('DELETE FROM stats');
        $db->execute('DELETE FROM users');
    }
    seedUsers($db);
    seedStats($db);
    seedInvoices($db);
    seedToggles($db);
    echo "✓ All seeders completed.\n";
}

/**
 * @param Connection $db
 */
function seedUsers(Connection $db, bool $force = false): void
{
    if ($force) {
        $db->execute('DELETE FROM users');
    }
    $users = [
        ['id' => 'usr_001', 'name' => 'Leo Daidone', 'email' => 'leo@example.com', 'role' => 'admin'],
        ['id' => 'usr_002', 'name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'user'],
        ['id' => 'usr_003', 'name' => 'Carol Williams', 'email' => 'carol@example.com', 'role' => 'user'],
        ['id' => 'usr_004', 'name' => 'Dave Brown', 'email' => 'dave@example.com', 'role' => 'user'],
        ['id' => 'usr_005', 'name' => 'Eve Davis', 'email' => 'eve@example.com', 'role' => 'viewer'],
    ];

    foreach ($users as $user) {
        $db->insert('users', $user);
    }
    echo "✓ Seeded 5 users.\n";
}

/**
 * @param Connection $db
 */
function seedStats(Connection $db, bool $force = false): void
{
    if ($force) {
        $db->execute('DELETE FROM stats');
    }
    $stats = [
        ['id' => 'revenue', 'label' => 'Revenue', 'value' => '$12,450', 'icon' => 'fa-dollar-sign', 'trend' => '+12%', 'up' => 1],
        ['id' => 'users', 'label' => 'Users', 'value' => '1,234', 'icon' => 'fa-users', 'trend' => '+5%', 'up' => 1],
        ['id' => 'orders', 'label' => 'Orders', 'value' => '567', 'icon' => 'fa-cart-shopping', 'trend' => '-2%', 'up' => 0],
        ['id' => 'conversion', 'label' => 'Conversion', 'value' => '3.2%', 'icon' => 'fa-chart-line', 'trend' => '+0.8%', 'up' => 1],
    ];

    foreach ($stats as $stat) {
        $db->insert('stats', $stat);
    }
    echo "✓ Seeded 4 stats.\n";
}

/**
 * @param Connection $db
 */
function seedInvoices(Connection $db, bool $force = false): void
{
    if ($force) {
        $db->execute('DELETE FROM invoices');
    }
    $invoices = [
        ['id' => 'inv_001', 'customer_name' => 'Acme Corp', 'amount_cents' => 150000, 'currency' => 'USD', 'issued_at' => date('Y-m-d'), 'status' => 'paid'],
        ['id' => 'inv_002', 'customer_name' => 'TechStart Inc', 'amount_cents' => 75000, 'currency' => 'USD', 'issued_at' => date('Y-m-d', strtotime('-1 day')), 'status' => 'pending'],
        ['id' => 'inv_003', 'customer_name' => 'Global Ltd', 'amount_cents' => 225000, 'currency' => 'USD', 'issued_at' => date('Y-m-d', strtotime('-2 days')), 'status' => 'paid'],
    ];

    foreach ($invoices as $invoice) {
        $db->insert('invoices', $invoice);
    }
    echo "✓ Seeded 3 invoices.\n";
}

/**
 * @param Connection $db
 */
function seedToggles(Connection $db, bool $force = false): void
{
    if ($force) {
        $db->execute('DELETE FROM toggles');
    }
    $toggles = [
        ['id' => 'dark_mode', 'label' => 'Dark Mode', 'enabled' => 1],
        ['id' => 'notifications', 'label' => 'Notifications', 'enabled' => 1],
        ['id' => 'analytics', 'label' => 'Analytics', 'enabled' => 0],
    ];

    foreach ($toggles as $toggle) {
        $db->insert('toggles', $toggle);
    }
    echo "✓ Seeded 3 toggles.\n";
}