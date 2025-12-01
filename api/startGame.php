<?php
// TODO: добавить защиты для входных значений
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE с обычными таблицами и только SELECT для ref_таблиц
// TODO: возможно, вынести этот блок глобально(кроме auth.php)
try {
    $curuserId = null;
    if (isset($_COOKIE['auth_token'])) {
        $authToken = $_COOKIE['auth_token'];
        $stmt = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE auth_token = ?
        ");
        $stmt->execute([$authToken]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $curuserId = $user['id'];
    }
    if (empty($curuserId)) {
        throw new Exception("auth error", 1);
    }
}
catch(ex){
    http_response_code(401);
    echo json_encode(['error' => 'Access allowed only for registered users']);
}

if (function_exists(explode('/', $path)[2]) && !str_starts_with(explode('/', $path)[2], '__')) {
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
            ,us.username 'player_name'
            ,sps.ready_for_start
            ,fs.id 'faction_id'
            ,fs.name 'faction_name'
            ,cs.id 'color_id'
            ,cs.code 'color_code'
        from sessions ss
        join session_players sps on sps.session_id = ss.id
        join users us on us.id = sps.user_id
        join ref_factions fs on fs.id = sps.faction_id
        join ref_colors cs on cs.id = sps.color_id
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
                'faction_id' => $row['faction_id'],
                'faction_name' => $row['faction_name'],
                'color_id' => $row['color_id'],
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
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        $sessionName = trim($_GET['session_name']);
        if (empty($sessionName) || empty($factionId) || empty($colorId))
        {
            throw new Exception("wrong_param", 1);
        }

        $newSession = __createSession();
        if (empty($newSession['result']))
        {
            throw new Exception("error_create_session", 1);
        }
        $newSessionPlayer = __createSessionPlayer(['session_id' => $newSession['result']['session_id']]);

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
            throw new Exception("wrong_param", 1);
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

function createSessionPlayer() {
    $res = ['error' => null, 'result' => null];
    try {
        $sessionId = trim($_GET['session_id']);
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        if (empty($sessionId) || !ctype_digit($sessionId) || empty($factionId) || !ctype_digit($factionId) || empty($colorId) || !ctype_digit($colorId))
        {
            throw new Exception("wrong_param", 1);
        }

        $newSessionPlayer = __createSessionPlayer(['session_id' => $sessionId]);
        $res['result'] = $newSessionPlayer;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}
// TODO: перед созданием проверить, что выбранная фракция и цвет всё еще доступны. Если нет - вернуть читаемую ошибку на фронт о том,
// что фракция/цвет более недоступна и обновить там список
// В теории можно оставить до подключения веб-сокетов 
function __createSessionPlayer($data = []) {
    $res = ['error' => null, 'result' => null];
    try {
        global $pdo, $curuserId;
        // параметр, который нужно прокинуть для отладки
        $echo = $data['echo'] ?? false;
        $sessionId = $data['session_id'];
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        if (empty($sessionId) || !ctype_digit($sessionId) || empty($factionId) || !ctype_digit($factionId) || empty($colorId) || !ctype_digit($colorId))
        {
            throw new Exception("wrong_param", 1);
        }
        
        $stmt = $pdo->prepare("insert into session_players(user_id, session_id, color_id, faction_id) values(?, ?, ?, ?)");
        $stmt->execute([$curuserId, $sessionId, $colorId, $factionId]);
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

// TODO: выводить все возможные опции, но отключив уже выбранные кем-то
function getFreeFactions() {
    $res = ['error' => null, 'result' => []];
    try {
        global $pdo, $curuserId;
        $sessionId = trim($_GET['session_id'] ?? 0);
        if (!ctype_digit($sessionId))
        {
            throw new Exception("wrong_param", 1);
        }

        $stmt = $pdo->prepare("
            select 
                fs.*
            from ref_factions fs
            left join session_players sp on sp.faction_id = fs.id
                and sp.session_id = ?
            where sp.faction_id is null
                or sp.user_id = ?
            order by fs.id
        ");
        $stmt->execute([$sessionId, $curuserId]);
        $factions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res['result'] = $factions;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}
// TODO: выводить все возможные опции, но отключив уже выбранные кем-то
function getFreeColors() {
    $res = ['error' => null, 'result' => []];
    try {
        global $pdo, $curuserId;
        $sessionId = trim($_GET['session_id'] ?? 0);
        if (!ctype_digit($sessionId))
        {
            throw new Exception("wrong_param", 1);
        }
        $stmt = $pdo->prepare("
            select 
                cs.*
            from ref_colors cs
            left join session_players sp on sp.color_id = cs.id
                and sp.session_id = ?
            where sp.color_id is null
                    or sp.user_id = ?
            order by cs.id
        ");
        $stmt->execute([$sessionId, $curuserId]);
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

