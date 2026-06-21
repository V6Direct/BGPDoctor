<?php

declare(strict_types=1);

/**
 * Minimal seed
 */

require __DIR__ . '/../bootstrap/app.php';
require __DIR__ . '/app/Helpers/functions.php';

use App\Config\Database;
use App\Models\User;

$db = Database::connection();


// Default router groups
$db->exec("INSERT OR IGNORE INTO router_groups (id, name, description) VALUES
    (1, 'Core', 'Core backbone routers'),
    (2, 'Edge', 'Internet edge routers'),
    (3, 'IX',   'IXP / peering routers')");

// first api key
$db->exec("INSERT OR IGNORE INTO agent_api_keys (id, label, api_key, is_active) VALUES
    (1, 'Default agent key', 'bgpdoctor_change_me_before_production', 1)");

//Admin user
$user = new User();
if (!$user->findByEmail('admin@v6direct.org')) {
    $user->create('Admin', 'admin@v6direct.org', 'Changeme', 'admin');
    echo "Admin user created (admin@v6direct.org / Changeme)\n";
} else {
    echo "Admin user already exists, skipping.\n";
}

echo "Seed completed.\n";
echo "Deploy an agent and POST data.\n";