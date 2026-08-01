<?php
/**
 * Conexión a la base de datos usando PDO.
 * Ajustar credenciales según el entorno.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'lovestory');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
/*define('DB_HOST', 'sql110.infinityfree.com:3306');
define('DB_NAME', 'if0_42550079_lovestory');
define('DB_USER', 'if0_42550079');
define('DB_PASS', 'Monkimin92');
define('DB_CHARSET', 'utf8mb4'); */

function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    return $pdo;
}
