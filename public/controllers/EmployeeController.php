<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../dal/src/Repositories/EmployeeRepository.php';

class EmployeeController extends BaseController {
    private $repo;
    public function __construct(){parent::__construct();$this->repo=new EmployeeRepository($this->pdo);}
    // Методы listAction, createAction, editAction, deleteAction, viewAction — аналогично ClientController
    // Валидация: проверка телефона, email, hire_date не в будущем, salary > 0
    // При удалении: проверка bookings WHERE employee_id=?
}