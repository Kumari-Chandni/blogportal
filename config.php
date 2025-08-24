<?php
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost:3308');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'blog_system');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'fallback-secret-key');

define('PIXABAY_API_KEY', $_ENV['PIXABAY_API_KEY'] ?? '');

define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost:8000');

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('UTC');
?>