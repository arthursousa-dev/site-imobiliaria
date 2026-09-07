<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instancia = null;

    public static function getConnection(): PDO
    {
        if (self::$instancia === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            $nome = getenv('DB_NAME') ?: 'horizonte';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';

            $dsn = "mysql:host={$host};port={$port};dbname={$nome};charset=utf8mb4";

            try {
                self::$instancia = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                die('Não foi possível conectar ao banco de dados.');
            }
        }

        return self::$instancia;
    }
}
