<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: access, Authorization, Content-Type');
    header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 86400');
    header('Content-Type: application/json; charset=UTF-8');

    // Respond to CORS preflight (OPTIONS) so browser allows actual request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    // Load .env from API root (works when headers.php is included from subdirs)
    $envFile = __DIR__ . '/.env';
    if (is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, " \t\"'");
                if ($key !== '') putenv("$key=$val");
            }
        }
    }

include "mydbCon.php";
?>