<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Database Configuration & PDO Connection Singleton (Q4 & Q5)
 */

declare(strict_types=1);

// Application Database & Runtime Configuration
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'pharmacare';
const DB_USER = 'root';
const DB_PASS = '';
const APP_TIMEZONE = 'Asia/Dhaka';
const APP_NAME = 'PharmaCare';

date_default_timezone_set(APP_TIMEZONE);

/**
 * Returns the singleton PDO database connection instance.
 * Employs strict error reporting and prepared statements to prevent SQL Injection (Q5).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $pdo->exec("SET time_zone = '+06:00'");
        } catch (PDOException $e) {
            http_response_code(500);
            die("
            <!doctype html>
            <html lang='en'>
            <head>
                <meta charset='utf-8'>
                <title>Database Connection Issue | " . APP_NAME . "</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; }
                    .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                    h2 { color: #dc2626; margin-top: 0; font-size: 20px; }
                    .alert { background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
                    ul { padding-left: 20px; font-size: 14px; line-height: 1.6; }
                    code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; color: #0f172a; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <h2>Database Connection Failed</h2>
                    <div class='alert'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>
                    <p>Please check your database credentials in <code>config/database.php</code>:</p>
                    <ul>
                        <li><strong>Host:</strong> <code>" . DB_HOST . ":" . DB_PORT . "</code></li>
                        <li><strong>Database:</strong> <code>" . DB_NAME . "</code></li>
                        <li><strong>User:</strong> <code>" . DB_USER . "</code></li>
                    </ul>
                    <p style='font-size: 13px; color: #64748b;'>Ensure MySQL / MariaDB is running and <code>database/pharmacare.sql</code> has been imported.</p>
                </div>
            </body>
            </html>
            ");
        }
    }

    return $pdo;
}
