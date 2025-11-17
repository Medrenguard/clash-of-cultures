<?php
// TODO: эта конструкция catch не все ошибки ловит, к сожалению, нужно подумать
// TODO: добавить защиту от иньекций
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
        $nickname = $_GET['nickname'];
        $faction_id = $_GET['faction_id'];
        $color_id = $_GET['color_id'];
        $sessionName = $_GET['session_name'];
        if (empty($nickname) || empty($sessionName) || empty($faction_id) || empty($color_id))
        {
            throw new Exception("Wrong param", 1);
        }

        // создаём сессию с невыводом echo
        $newSession = __createSession(false);
        if (empty($newSession['result']))
        {
            throw new Exception("Error create session", 1);
        }
        // создаём игрока с невыводом echo
        $newSessionPlayer = __createSessionPlayer($newSession['result']['session_id'], 1, false);

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

function __createSession($echo_result = true) {
    $res = ['error' => null, 'result' => null];
    try {
        global $pdo;
        $sessionName = $_GET['session_name'];
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
    if ($echo_result) {
        echo json_encode($res);
    }
    return $res;
}
function __createSessionPlayer($sessionId, $isCreator, $echo_result = true) {
    $res = ['error' => null, 'result' => null];
    try {
        global $pdo;
        $nickname = $_GET['nickname'];
        $faction_id = $_GET['faction_id'];
        $color_id = $_GET['color_id'];
        if (empty($nickname) || empty($sessionId) || empty($faction_id) || empty($color_id))
        {
            throw new Exception("Wrong param", 1);
        }
        
        $stmt = $pdo->prepare("insert into session_players(name, session_id, color_id, faction_id, is_creator) values(?, ?, ?, ?, ?)");
        $stmt->execute([$nickname, $sessionId, $color_id, $faction_id, $isCreator]);
        $playerId = $pdo->lastInsertId();
        $res['result'] = $playerId;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    if ($echo_result) {
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

