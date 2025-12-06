<?php
// Получает и проверяет информацию о параметре session_id из GET-запроса, если сессия не указана или не найдена - возвращает ошибку
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE с обычными таблицами и только SELECT для ref_таблиц
try {
    global $pdo;
    $cursession = null;
    $sessionId = isset($_GET['session_id']) ? trim($_GET['session_id']) : '';
    if (empty($sessionId))
    {
        throw new Exception("Не указан id сессии", 1);
    }
    if (!ctype_digit($sessionId) || (int)$sessionId <= 0) {
        throw new Exception("Некорректный id сессии", 1);
    }
    $stmt = $pdo->prepare("SELECT id FROM sessions WHERE id = ?");
    $stmt->execute([$sessionId]);
    $cursession = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cursession) {
        throw new Exception("Сессия не найдена", 1);
    }
}
catch(Exception $ex){
    http_response_code(400); 
    echo json_encode(['error' => ['message' => $ex->getMessage()]]);
    exit;
}
?>

