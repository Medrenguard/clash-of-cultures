<?php
// роутинг, вызывает целевую функцию, если она в файле и не внутренняя, иначе - ошибка. Для 2 уровня вложенности
// TODO: при появлении 3 уровня нужно будет что-то придумать
if (function_exists(explode('/', $path)[2]) && !str_starts_with(explode('/', $path)[2], '__')) {
    call_user_func(explode('/', $path)[2]);
} else {
    // 404 - маршрут не найден
    http_response_code(404);
    echo json_encode(['error' => 'Функция/роут не найдены']);
}
?>