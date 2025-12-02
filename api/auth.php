<?php
// TODO: добавить защиты для входных значений
// TODO: МБ добавить отдельного технического юзера для работы с базой, без суперправ, только INSERT, SELECT, UPDATE
if (function_exists(explode('/', $path)[2])) {
    call_user_func(explode('/', $path)[2]);
} else {
    // 404 - маршрут не найден
    http_response_code(404);
    echo json_encode(['error' => 'Function/route not found']);
}

function checkAuth() {
    $res = [
        'error' => null,
        'result' => [
            'username' => null
        ]
    ];
    try {
        // TODO: подумать о выносе в отдельный файл этого конфига для POST-функций
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON format", 1);
        }
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if (empty($username) || empty($password))
        {
            throw new Exception("Неверный параметр", 1);
        }
        global $pdo;
        $stmt = $pdo->prepare("
        select
        	id
            ,username
            ,password_hash
        from users
        where username = ?
            and is_active
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Генерируем уникальный токен
            $authToken = bin2hex(random_bytes(32)).'_'.$user['id'];
            // Сохраняем токен в базу
            $updateStmt = $pdo->prepare("update users set auth_token = ? where id = ?");
            $updateStmt->execute([$authToken, $user['id']]);
            // Устанавливаем куку
            setcookie('auth_token', $authToken, [
                'expires' => time() + (7 * 24 * 3600), // 7 дней
                'path' => '/',
                'secure' => false, // TODO true в production
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            $res['result'] = [
                'username' => $user['username']
            ];
        };
    }
    catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    echo json_encode($res);
    return $res;
}
// TODO: когда-нибудь в теории сделать информацию о том, что ваш аккаунт неактивен
function checkAuthStatus() {
    $res = [
        'error' => null,
        'result' => [
            'authenticated' => false,
            'username' => null
        ]
    ];
    try {
        if (isset($_COOKIE['auth_token'])) {
            $authToken = $_COOKIE['auth_token'];
            
            global $pdo;
            $stmt = $pdo->prepare("
                SELECT username 
                FROM users 
                WHERE auth_token = ? AND is_active = 1
            ");
            $stmt->execute([$authToken]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $res['result'] = [
                    'authenticated' => true,
                    'username' => $user['username']
                ];
            }
        }
    } catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    
    echo json_encode($res);
    return $res;
}

function logout() {
    $res = ['error' => null, 'result' => false];
    try {
        if (isset($_COOKIE['auth_token'])) {
            $authToken = $_COOKIE['auth_token'];
            
            global $pdo;
            $stmt = $pdo->prepare("update users set auth_token = null where auth_token = ?");
            $stmt->execute([$authToken]);
            
            // Удаляем куку
            setcookie('auth_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => false, // TODO true в production
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        $res['result'] = true;
    } catch(Exception $ex) {
        $res['error'] = ['message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()];
    }
    
    echo json_encode($res);
    return $res;
}
?>

