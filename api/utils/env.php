<?php
function env($key, $default = null) {
    static $env = null;
    
    if ($env === null) {
        $env = parse_ini_file(__DIR__ . '/../.env.local');
    }
    
    return $env[$key] ?? $default;
}
?>