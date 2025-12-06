<?php
// TODO: добавить защиты для входных значений
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE с обычными таблицами и только SELECT для ref_таблиц
require_once 'globals/cookie/curuser.php';
require_once 'globals/routing.php';

function createSession() {
    $res = ['error' => null, 'result' => null];
    try {
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        $sessionName = trim($_GET['session_name']);
        if (empty($sessionName) || empty($factionId) || empty($colorId))
        {
            throw new Exception("Неверный параметр", 1);
        }

        $newSession = __createSession();
        if (empty($newSession['result']))
        {
            throw new Exception("Ошибка создания сессии", 1);
        }

        $originalGet = $_GET;
        // добрасывает GET-параметр для вызова функции из файла, где без этого параметра в GET ничего не заработает
        try {
            $_GET['session_id'] = $newSession['result']['session_id'];
            require_once 'joinGame.php';
            $newSessionPlayer = __createSessionPlayer(['session_id'=> $_GET['session_id'], 'faction_id' => $factionId,'color_id'=> $colorId]);
        }
        finally {
            $_GET = $originalGet;
        }

        $res['result'] = [
            'session_id' => $newSession['result']['session_id'], 
            'redirect_url' => '/game/' . $newSession['result']['session_id'],
            'player_id' => $newSessionPlayer['result']
        ];
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}

function __createSession($data = []) {
    $res = ['error' => null, 'result' => null];
    try {
        global $pdo, $curuserId;
        // параметр, который нужно прокинуть для отладки
        $echo = $data['echo'] ?? false;
        $sessionName = trim($_GET['session_name']);
        if (empty($sessionName))
        {
            throw new Exception("Неверный параметр", 1);
        }

        $stmt = $pdo->prepare("insert into sessions(name, creator_user_id) values(?, ?)");
        $stmt->execute([$sessionName, $curuserId]);
        $sessionId = $pdo->lastInsertId();
        
        $res['result'] = [
            'session_id' => $sessionId
        ];
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    if ($echo) {
        echo json_encode($res);
    }
    return $res;
}

function getFactions() {
    $res = ['error' => null, 'result' => []];
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            select 
                fs.*
            from ref_factions fs
            order by fs.id
        ");
        $stmt->execute();
        $factions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res['result'] = $factions;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}

function getColors() {
    $res = ['error' => null, 'result' => []];
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            select 
                cs.*
            from ref_colors cs
            order by cs.id
        ");
        $stmt->execute();
        $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res['result'] = $colors;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}
?>

