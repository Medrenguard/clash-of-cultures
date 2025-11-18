<?php
// TODO: добавить защиты для входных значений
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE
if (strpos($path, '/startGame/CreateSession') !== false) {
    createSession();
} elseif (strpos($path, '/startGame/getFreeFactions') !== false) {
    getFreeFactions();
} elseif (strpos($path, '/startGame/getFreeColors') !== false) {
    getFreeColors();
} else {
    // 404 - маршрут не найден
    http_response_code(404);
    echo json_encode(['error' => 'Function/route not found']);
}

function createSession() {
    $res = ['error' => null, 'result' => null];
    try {
        $nickname = trim($_GET['nickname']);
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        $sessionName = trim($_GET['session_name']);
        if (empty($nickname) || empty($sessionName) || empty($factionId) || empty($colorId))
        {
            throw new Exception("Wrong param", 1);
        }

        $newSession = __createSession();
        if (empty($newSession['result']))
        {
            throw new Exception("Error create session", 1);
        }
        $newSessionPlayer = __createSessionPlayer(['session_id' => $newSession['result']['session_id'], 'is_creator' => 1], 1);

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
        global $pdo;
        // параметр, который нужно прокинуть для отладки
        $echo = $data['echo'] ?? false;
        $sessionName = trim($_GET['session_name']);
        if (empty($sessionName))
        {
            throw new Exception("Wrong param", 1);
        }

        $stmt = $pdo->prepare("insert into sessions(name) values(?)");
        $stmt->execute([$sessionName]);
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
function __createSessionPlayer($data = []) {
    $res = ['error' => null, 'result' => null];
    try {
        global $pdo;
        // параметр, который нужно прокинуть для отладки
        $echo = $data['echo'] ?? false;
        $sessionId = $data['session_id'];
        $isCreator = $data['is_creator'] ?? 0;
        $nickname = trim($_GET['nickname']);
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        if (empty($nickname) || empty($sessionId) || empty($factionId) || empty($colorId) || empty($isCreator))
        {
            throw new Exception("Wrong param", 1);
        }
        
        $stmt = $pdo->prepare("insert into session_players(name, session_id, color_id, faction_id, is_creator) values(?, ?, ?, ?, ?)");
        $stmt->execute([$nickname, $sessionId, $colorId, $factionId, $isCreator]);
        $playerId = $pdo->lastInsertId();
        $res['result'] = $playerId;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    if ($echo) {
        echo json_encode($res);
    }
    return $res;
}
function getFreeFactions() {
    $res = ['error' => null, 'result' => []];
    try {
        global $pdo;
        $stmt = $pdo->prepare("select * from factions");
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
function getFreeColors() {
    $res = ['error' => null, 'result' => []];
    try {
        global $pdo;
        $stmt = $pdo->prepare("select * from colors");
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

