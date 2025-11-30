<?php
// Добавить проверку на авторизацию перед любыми действиями в этом файле вне функций, вытаскивать данные по авторизации на основе auth_token
// TODO: добавить защиты для входных значений
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE
// TODO: здесь и во всех файлах сделать так, чтобы внутренние функции нельзя было дёргать с фронта
if (function_exists(explode('/', $path)[2])) {
    call_user_func(explode('/', $path)[2]);
} else {
    // 404 - маршрут не найден
    http_response_code(404);
    echo json_encode(['error' => 'Function/route not found']);
}

function getSessionInfo() {
    $res = ['error' => null, 'result' => ['name' => null, 'players' => []]];
    try {
        global $pdo;
        $sessionId = trim($_GET['session_id']);
        if (empty($sessionId) || !ctype_digit($sessionId))
        {
            throw new Exception("wrong_param", 1);
        }

        $stmt = $pdo->prepare("
        select 
            ss.name
            ,sps.id 'player_id'
            ,sps.name 'player_name'
            ,sps.ready_for_start
            ,fs.name 'faction_name'
            ,cs.code 'color_code'
        from sessions ss
        join session_players sps on sps.session_id = ss.id
        join factions fs on fs.id = sps.faction_id
        join colors cs on cs.id = sps.color_id
        where ss.id = ?
        ");
        $stmt->execute([$sessionId]);
        $sessionInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        If (count($sessionInfo) == 0) throw new Exception("game_not_found", 1);
        $res['result']['name'] = $sessionInfo[0]['name'];
        foreach ($sessionInfo as $row) {
            $res['result']['players'][] = [
                'id' => $row['player_id'],
                'name' => $row['player_name'],
                'ready_for_start' => $row['ready_for_start'],
                'faction_name' => $row['faction_name'],
                'color_code' => $row['color_code']
            ];
        }
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
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
            throw new Exception("wrong_param", 1);
        }

        $newSession = __createSession();
        if (empty($newSession['result']))
        {
            throw new Exception("error_create_session", 1);
        }
        $newSessionPlayer = __createSessionPlayer(['session_id' => $newSession['result']['session_id'], 'is_creator' => 1]);

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
            throw new Exception("wrong_param", 1);
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

function createSessionPlayer() {
    $res = ['error' => null, 'result' => null];
    try {
        $sessionId = trim($_GET['session_id']);
        $nickname = trim($_GET['nickname']);
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        if (empty($nickname) || empty($sessionId) || !ctype_digit($sessionId) || empty($factionId) || !ctype_digit($factionId) || empty($colorId) || !ctype_digit($colorId))
        {
            throw new Exception("wrong_param", 1);
        }

        $newSessionPlayer = __createSessionPlayer(['session_id' => $sessionId, 'is_creator' => 0]);
        $res['result'] = $newSessionPlayer;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
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
        if (empty($nickname) || empty($sessionId) || !ctype_digit($sessionId) || empty($factionId) || !ctype_digit($factionId) || empty($colorId) || !ctype_digit($colorId))
        {
            throw new Exception("wrong_param", 1);
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
        $sessionId = trim($_GET['session_id'] ?? 0);
        if (!ctype_digit($sessionId))
        {
            throw new Exception("wrong_param", 1);
        }

        $stmt = $pdo->prepare("
            select 
                fs.*
            from factions fs
            left join session_players sp on sp.faction_id = fs.id
                and sp.session_id = ?
            where sp.faction_id is null
            order by fs.id
        ");
        $stmt->execute([$sessionId]);
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
        $sessionId = trim($_GET['session_id'] ?? 0);
        if (!ctype_digit($sessionId))
        {
            throw new Exception("wrong_param", 1);
        }
        $stmt = $pdo->prepare("
        select 
            cs.*
        from colors cs
        left join session_players sp on sp.color_id = cs.id
            and sp.session_id = ?
        where sp.color_id is null
        order by cs.id
        ");
        $stmt->execute([$sessionId]);
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

