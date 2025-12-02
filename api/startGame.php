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
        throw new Exception("Ошибка аутентификации", 1);
    }
}
catch(ex){
    http_response_code(401);
    echo json_encode(['error' => 'Доступ только для зарегистрированных пользователей']);
}

if (function_exists(explode('/', $path)[2]) && !str_starts_with(explode('/', $path)[2], '__')) {
    call_user_func(explode('/', $path)[2]);
} else {
    // 404 - маршрут не найден
    http_response_code(404);
    echo json_encode(['error' => 'Функция/роут не найдены']);
}

function getSessionInfo() {
    $res = ['error' => null, 'result' => ['name' => null, 'players' => []]];
    try {
        global $pdo;
        $sessionId = trim($_GET['session_id']);
        if (empty($sessionId) || !ctype_digit($sessionId))
        {
            throw new Exception("Неверный параметр", 1);
        }

        $stmt = $pdo->prepare("
        select 
            ss.name
            ,creator.username 'creator_username'
            ,ss.is_started
            ,sps.id 'player_id'
            ,us.username 'player_name'
            ,sps.ready_for_start
            ,fs.id 'faction_id'
            ,fs.name 'faction_name'
            ,cs.id 'color_id'
            ,cs.code 'color_code'
        from sessions ss
        join users creator on creator.id = ss.creator_user_id
        join session_players sps on sps.session_id = ss.id
        join users us on us.id = sps.user_id
        join ref_factions fs on fs.id = sps.faction_id
        join ref_colors cs on cs.id = sps.color_id
        where ss.id = ?
        ");
        $stmt->execute([$sessionId]);
        $sessionInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        If (count($sessionInfo) == 0) throw new Exception("Игра не найдена", 1);
        $res['result'] = [
            'name'=> $sessionInfo[0]['name'],
            'creator_username' => $sessionInfo[0]['creator_username'],
            'is_started' => !!$sessionInfo[0]['is_started'],
        ];
        foreach ($sessionInfo as $row) {
            $res['result']['players'][] = [
                'id' => $row['player_id'],
                'name' => $row['player_name'],
                'ready_for_start' => !!$row['ready_for_start'],
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
            throw new Exception("Неверный параметр", 1);
        }

        $newSession = __createSession();
        if (empty($newSession['result']))
        {
            throw new Exception("Ошибка создания сессии", 1);
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

function createSessionPlayer() {
    $res = ['error' => null, 'result' => null];
    try {
        $sessionId = trim($_GET['session_id']);
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        if (empty($sessionId) || !ctype_digit($sessionId) || empty($factionId) || !ctype_digit($factionId) || empty($colorId) || !ctype_digit($colorId))
        {
            throw new Exception("Неверный параметр", 1);
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
            throw new Exception("Неверный параметр", 1);
        }
        
        // проверка максимального количества игроков и статуса игры
        $checkStmt = $pdo->prepare("
            select 
                s.is_started,
                count(sp.id) as players_count
            from sessions s
            left join session_players sp on sp.session_id = s.id
            where s.id = ?
            group by s.id
        ");
        $checkStmt->execute([$sessionId]);
        $sessionData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sessionData) {
            throw new Exception("Сессия не найдена", 1);
        }
        
        if (!!$sessionData['is_started']) {
            throw new Exception("Игра уже начата, нельзя присоединиться", 1);
        }
        
        if ($sessionData['players_count'] >= 4) {
            throw new Exception("В комнате уже максимальное количество игроков (4)", 1);
        }
        
        // проверка, не участвует ли уже пользователь
        $playerExistsStmt = $pdo->prepare("select id from session_players where user_id = ? and session_id = ?");
        $playerExistsStmt->execute([$curuserId, $sessionId]);
        
        if ($playerExistsStmt->fetch()) {
            throw new Exception("Вы уже участвуете в этой игре", 1);
        }
        
        // проверка занятости фракции и цвета
        $occupiedStmt = $pdo->prepare("
            select 
                (select id from session_players where session_id = ? and faction_id = ?) as occupied_faction,
                (select id from session_players where session_id = ? and color_id = ?) as occupied_color
        ");
        $occupiedStmt->execute([$sessionId, $factionId, $sessionId, $colorId]);
        $occupied = $occupiedStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($occupied['occupied_faction']) {
            throw new Exception("Эта фракция уже занята другим игроком", 1);
        }
        
        if ($occupied['occupied_color']) {
            throw new Exception("Этот цвет уже занят другим игроком", 1);
        }
        
        // все проверки пройдены - создаем игрока
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
            throw new Exception("Неверный параметр", 1);
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
            throw new Exception("Неверный параметр", 1);
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

function setReadyPlayer() {
    $res = ['error' => null, 'result' => false];
    try {
        global $pdo, $curuserId;
        $sessionId = trim($_GET['session_id'] ?? 0);
        if (!ctype_digit($sessionId))
        {
            throw new Exception("Неверный параметр", 1);
        }
        $stmt = $pdo->prepare("
            set @player_id = (select id from session_players where user_id = ? and session_id = ? limit 1);

            update session_players
            set ready_for_start = 1
            where id = @player_id;
        ");
        $stmt->execute([$curuserId, $sessionId]);
        $affectedRows = $stmt->rowCount();
        if ($affectedRows == 1) {
            $res['result'] = true;
        } else {
            throw new Exception("Ошибка установки готовности игрока", 1);
        }
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}

// TODO: функция должна еще переводить на новый статус документоборота игру или этот статус должен в принципе появляться
// Документооборот нужно завести
function startGame() {
    $res = ['error' => null, 'result' => false];
    try {
        global $pdo, $curuserId;
        $sessionId = trim($_GET['session_id'] ?? 0);
        if (!ctype_digit($sessionId))
        {
            throw new Exception("Неверный параметр", 1);
        }
        $stmt = $pdo->prepare("
            set @players_count = (select count(*) from session_players sps where sps.session_id = ?);
            set @ready_players_count = (select count(*) from session_players sps where sps.session_id = ? and sps.ready_for_start = 1);

            update sessions
            set is_started = 1
            where id = ?
                and creator_user_id = ?
                and @players_count between 2 and 4
                and @ready_players_count = @players_count
        ");
        $stmt->execute([$sessionId, $sessionId, $sessionId, $curuserId]);
        $affectedRows = $stmt->rowCount();
        if ($affectedRows == 1) {
            $res['result'] = true;
        } else {
            throw new Exception("Ошибка старта игры", 1);
        }
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}

?>

