<?php

declare(strict_types=1);

namespace App\Config;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function intitialize(): void
    {
        if (self::$connection !== null) {
            return;
        }

        $dbPath   = Env::get('DB_PATH', 'database/bgpdoctor.sqlite');
        $absolute = dirname(__DIR__, 2) . '/' . ltrim($dbPath, '/');
        $dir      = dirname($absolute);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        self::$connection = new PDO('sqlite:' . $absolute);
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        self::$connection->exec('PRAGMA foreign_keys = ON');
    }

        public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::intitialize();
        }
        return self::connection();
    }
} // pretty sure some1 once told me not to use PDO anymore, but i cant remember sooo
