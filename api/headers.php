<?php
require_once 'utils/env.php';
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // мб тут добавить http_response_code(200);, нужно для отправки json, проверить это
    exit(0);
}

// Подключение к БД
try {
    $pdo = new PDO(
        "mysql:host=".env('DATABASE_host').";dbname=".env('DATABASE_name').";charset=utf8",
        env('DATABASE_username'),
        env('DATABASE_password')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Получаем путь запроса
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$queryString = parse_url($requestUri, PHP_URL_QUERY);
?>