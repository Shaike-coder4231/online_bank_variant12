<?php
abstract class BaseController {
    protected $pdo;
    public function __construct() {
        require_once __DIR__ . '/../../config/db.php';
        $this->pdo = getPDO();
    }
    protected function initCSRF() {
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
    protected function verifyCSRF() {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) die('Ошибка безопасности');
    }
    protected function setFlash($type, $message) { $_SESSION['flash'] = ['type' => $type, 'message' => $message]; }
    protected function getFlash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
    
    // Валидаторы для банка
    protected function isPassport($p) { return preg_match('/^\d{4}\s?\d{6}$/', preg_replace('/\s+/', '', $p)) === 1; }
    protected function isPhone($p) { return preg_match('/^[\+\d\s\-\(\)]{10,20}$/', trim($p)) === 1; }
    protected function isEmail($e) { return filter_var(trim($e), FILTER_VALIDATE_EMAIL) !== false; }
    protected function isPastDate($d) { return !empty($d) && strtotime($d) <= time(); }
    protected function isAdult($d) { return !empty($d) && date_diff(new DateTime($d), new DateTime())->y >= 18; }
    protected function isPositiveAmount($a) { return is_numeric($a) && floatval($a) >= 0; }
}