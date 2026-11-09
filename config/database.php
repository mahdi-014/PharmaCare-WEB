<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Database Configuration & PDO Connection Singleton (Q4 & Q5)
 */

declare(strict_types=1);

// Application Database & Runtime Configuration with Environment Variable Support
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'pharmacare');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Dhaka');
define('APP_NAME', getenv('APP_NAME') ?: 'PharmaCare');

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
            error_log('Database Connection Error: ' . $e->getMessage());
            http_response_code(500);
            die("
            <!doctype html>
            <html lang='en'>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <title>Database Service Unavailable | " . htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') . "</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
                    .card { max-width: 520px; width: 100%; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 36px 32px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); text-align: center; }
                    .icon { width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px; }
                    h1 { color: #0f172a; margin: 0 0 12px; font-size: 22px; font-weight: 600; }
                    p { font-size: 15px; line-height: 1.6; color: #64748b; margin: 0 0 24px; }
                    .retry-btn { display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: background 0.2s; }
                    .retry-btn:hover { background: #0369a1; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <div class='icon'>&#9888;</div>
                    <h1>Database Service Temporarily Unavailable</h1>
                    <p>We are currently experiencing technical difficulties establishing a secure connection to our database service. Our technical team has been notified. Please try refreshing the page in a few moments.</p>
                    <a href='javascript:location.reload()' class='retry-btn'>Try Again</a>
                </div>
            </body>
            </html>
            ");
        }
    }

    return $pdo;
}
