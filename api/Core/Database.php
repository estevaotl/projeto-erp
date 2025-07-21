<?php
namespace Api\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
            $dotenv->load();

            try {
                self::$instance = new PDO(
                    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die('Erro ao conectar: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
