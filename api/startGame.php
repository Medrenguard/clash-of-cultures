<?php
// TODO: эта конструкция catch не все ошибки ловит, к сожалению, нужно подумать
if (strpos($path, '/startGame/CreateSession') !== false) {
    $newSession = createSession();
    createSessionPlayer($newSession['result']['session_id'], 1);
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
        global $pdo;
        $sessionName = $_GET['session_name'];
        
        $stmt = $pdo->prepare("insert into sessions(name) values(?)");
        $stmt->execute([$sessionName]);
        $sessionId = $pdo->lastInsertId();
        
        $res['result'] = [
            'session_id' => $sessionId, 
            'session_name' => $sessionName, 
            'redirect_url' => '/game/' . $sessionId
        ];
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}
function createSessionPlayer($sessionId, $isCreator) {
    $res = ['error' => null, 'result' => false];
    try {
        global $pdo;
        $nickname = $_GET['nickname'];
        $faction_id = $_GET['faction_id'];
        $color_id = $_GET['color_id'];
        
        $stmt = $pdo->prepare("insert into session_players(name, session_id, color_id, faction_id, is_creator) values(?, ?, ?, ?, ?)");
        $stmt->execute([$nickname, $sessionId, $color_id, $faction_id, $isCreator]);
        $res['result'] = true;
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
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

