<?php
session_start();
$page = $_GET['page'] ?? 'bookings/create';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Простая маршрутизация
$routes = [
    'bookings/create' => ['controller' => 'BookingController', 'method' => 'createAction'],
    'bookings/list' => ['controller' => 'BookingController', 'method' => 'listAction'],
    'bookings/view' => ['controller' => 'BookingController', 'method' => 'viewAction'],
    'bookings/reschedule' => ['controller' => 'BookingController', 'method' => 'rescheduleAction'],
    'bookings/cancel' => ['controller' => 'BookingController', 'method' => 'cancelAction'],
    'bookings/update_status' => ['controller' => 'BookingController', 'method' => 'updateStatusAction'],
    'reports' => ['controller' => 'ReportController', 'method' => 'indexAction'],
    'reports/export' => ['controller' => 'ReportController', 'method' => 'exportAction'],
];

if (isset($routes[$page])) {
    require_once __DIR__ . "/controllers/" . $routes[$page]['controller'] . ".php";
    $controller = new $routes[$page]['controller']();
    $method = $routes[$page]['method'];
    
    if ($id !== null) {
        $controller->$method((int)$id);
    } elseif ($action !== 'index') {
        $controller->$method($_GET);
    } else {
        $controller->$method();
    }
} else {
    http_response_code(404);
    echo "Страница не найдена. <a href='index.php?page=bookings/create'>На главную</a>";
}