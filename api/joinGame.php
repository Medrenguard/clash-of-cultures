<?php
// TODO: добавить защиты для входных значений
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE с обычными таблицами и только SELECT для ref_таблиц
require_once 'globals/cookie/curuser.php';
require_once 'globals/get/cursession.php';
require_once 'globals/routing.php';

function getSessionInfo() {
    $res = ['error' => null, 'result' => ['name' => null, 'players' => []]];
    try {
        global $pdo, $cursession;
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
        $stmt->execute([$cursession["id"]]);
        $sessionInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        If (count($sessionInfo) == 0) throw new Exception("Сессия не найдена", 1);
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

function createSessionPlayer() {
    $res = ['error' => null, 'result' => null];
    try {
        global $cursession;
        $factionId = trim($_GET['faction_id']);
        $colorId = trim($_GET['color_id']);
        if (empty($factionId) || !ctype_digit($factionId) || empty($colorId) || !ctype_digit($colorId))
        {
            throw new Exception("Неверный параметр", 1);
        }

        $newSessionPlayer = __createSessionPlayer(['session_id'=> $cursession['id'], 'faction_id'=> $factionId,'color_id'=> $colorId]);
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
        global $pdo, $curuserId;
        // параметр, который нужно прокинуть для отладки
        $echo = $data['echo'] ?? false;
        $sessionId = (string)$data['session_id'];
        $factionId = $data['faction_id'];
        $colorId = $data['color_id'];
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
        global $pdo, $curuserId, $cursession;
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
        $stmt->execute([$cursession['id'], $curuserId]);
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
        global $pdo, $curuserId, $cursession;
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
        $stmt->execute([$cursession['id'], $curuserId]);
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
        global $pdo, $curuserId, $cursession;
        $stmt = $pdo->prepare("
            set @player_id = (select id from session_players where user_id = ? and session_id = ? limit 1);

            update session_players
            set ready_for_start = 1
            where id = @player_id;
        ");
        $stmt->execute([$curuserId, $cursession["id"]]);
        $res['result'] = true;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}

// TODO: функция должна еще переводить на новый статус документоборота игру или этот статус должен в принципе появляться
// Документооборот нужно завести
// TODO: работает правильно, но можно оптимизировать
function startGame() {
    $res = ['error' => null, 'result' => false];
    try {
        global $pdo, $curuserId, $cursession;
        $firstPlayer = $_GET['first_player'] ?? null;
        if ($firstPlayer != null && !ctype_digit($firstPlayer))
        {
            throw new Exception("Неверный параметр", 1);
        }
        $stmt = $pdo->prepare("
            set @session_id = ?;
            set @first_player = (
                select
                    coalesce(selected_player.id, default_player.id) as id
                from sessions ss
                join users us on us.id = ss.creator_user_id
                join session_players default_player on default_player.user_id = us.id
                    and default_player.session_id = ss.id
                left join lateral (
                    select sps.id 
                    from session_players sps
                    where sps.session_id = @session_id 
                        and sps.id = ?
                ) selected_player on true
                where ss.id = @session_id
            );
            set @players_count = (select count(*) from session_players sps where sps.session_id = @session_id);
            set @ready_players_count = (select count(*) from session_players sps where sps.session_id = @session_id and sps.ready_for_start = 1);

            update sessions
            set
                is_started = 1
                ,first_player_id = @first_player
            where id = @session_id
                and creator_user_id = ?
                and @players_count between 2 and 4
                and @ready_players_count = @players_count
        ");
        $stmt->execute([$cursession['id'], $firstPlayer, $curuserId]);
        $res['result'] = true;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}

?>

