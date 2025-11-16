<?php
require_once 'headers.php';

// суперпримитивный роутинг по названию пути в нужный файл
require_once explode('/', $path)[1].'.php';
?>