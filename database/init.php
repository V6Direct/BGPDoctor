<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require __DIR__ . '/../app/Helpers/functions.php';

use App\Config\Database;

$sql = file_get_contents(__DIR__ . '/schema.sql');
Database::connection()->exec($sql);
echo "Database initialized\n";