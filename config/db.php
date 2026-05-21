<?php
// НАСТРОЙКИ БД — ЗАМЕНИТЕ НА СВОИ!
define('DB_HOST', 'localhost');
define('DB_NAME', 'beauty_salon_variant5');  // ← Ваша БД
define('DB_USER', 'root');                    // ← Ваш пользователь
define('DB_PASS', '');                        // ← Ваш пароль
define('DB_CHARSET', 'utf8mb4');

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

// Рабочие часы салона (константы)
define('WORK_START', '09:00');
define('WORK_END', '20:00');
define('LUNCH_START', '14:00');
define('LUNCH_END', '15:00');
define('SLOT_STEP', 30); // минут