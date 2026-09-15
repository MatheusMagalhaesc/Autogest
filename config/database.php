<?php
/**
 * Classe responsável por criar e devolver a conexão PDO com o banco de dados.
 * Padrão: SQLite, criado automaticamente na primeira execução (tabelas + admin de teste).
 * Também suporta MySQL (ex: rodando em XAMPP).
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        try {
            if (DB_DRIVER === 'sqlite') {
                $bancoNovo = !file_exists(DB_SQLITE_PATH);

                self::$instance = new PDO('sqlite:' . DB_SQLITE_PATH);
                self::$instance->exec('PRAGMA foreign_keys = ON');
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                if ($bancoNovo) {
                    $schema = __DIR__ . '/../database/schema_sqlite.sql';
                    if (file_exists($schema)) {
                        self::$instance->exec(file_get_contents($schema));
                    }
                }

                return self::$instance;
            }

            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Erro ao conectar ao banco de dados: ' . htmlspecialchars($e->getMessage()));
        }

        return self::$instance;
    }
}
