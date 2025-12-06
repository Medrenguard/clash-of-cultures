<?php
// Получает id текущего пользователя из кук, если нет - кидает ошибку
// TODO: возможно, в будущем добавить права сюда
try {
    global $pdo;
    $curuserId = null;
    if (isset($_COOKIE['auth_token'])) {
        $authToken = $_COOKIE['auth_token'];
        $stmt = $pdo->prepare("SELECT id FROM users WHERE auth_token = ?");
        $stmt->execute([$authToken]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $curuserId = $user['id'];
    }
    if (empty($curuserId)) {
        throw new Exception("Доступ только для зарегистрированных пользователей", 1);
    }
}
catch(Exception $ex){
    http_response_code(401);
    echo json_encode(['error' => ['message' => $ex->getMessage()]]);
    exit;
}
?>