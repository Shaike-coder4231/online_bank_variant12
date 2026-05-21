<?php
abstract class BaseController {
    protected $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/db.php';
        $this->pdo = getPDO();
    }
    
    // CSRF
    protected function initCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function verifyCSRF() {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die('Ошибка безопасности: CSRF-токен не совпадает.');
        }
    }
    
    // Flash-сообщения
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    
    protected function getFlash() {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
    
    // Генерация кода бронирования
    protected function generateBookingCode($id) {
        return 'BK-' . strtoupper(substr(md5($id . time() . random_int(1000,9999)), 0, 8));
    }
    
    // Валидаторы
    protected function isPhone($p) { return preg_match('/^[\+\d\s\-\(\)]{10,20}$/', trim($p)) === 1; }
    protected function isEmail($e) { return filter_var(trim($e), FILTER_VALIDATE_EMAIL) !== false; }
    protected function isFutureDate($d) { return !empty($d) && strtotime($d) >= strtotime('today'); }
    protected function isWorkingHours($time) {
        $t = strtotime($time);
        $start = strtotime(WORK_START); $end = strtotime(WORK_END);
        $lunchS = strtotime(LUNCH_START); $lunchE = strtotime(LUNCH_END);
        return ($t >= $start && $t < $lunchS) || ($t > $lunchE && $t <= $end);
    }
    
    // Экранирование
    protected function e($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
}