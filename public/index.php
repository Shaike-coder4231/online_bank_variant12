<?php
session_start();
$entity = $_GET['entity'] ?? 'client';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$controllerFile = __DIR__ . "/controllers/" . ucfirst($entity) . "Controller.php";
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $className = ucfirst($entity) . "Controller";
    $controller = new $className();

    switch ($action) {
        case 'list':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $sort = $_GET['sort'] ?? 'id';
            $order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
            $search = trim($_GET['search'] ?? '');
            $controller->listAction($page, $sort, $order, $search);
            break;
        case 'create': $controller->createAction(); break;
        case 'edit': $controller->editAction((int)$id); break;
        case 'delete': $controller->deleteAction((int)$id); break;
        case 'view': $controller->viewAction((int)$id); break;
        default: http_response_code(404); echo "Действие не найдено";
    }
} else {
    echo "Контроллер не найден. Проверьте параметр entity.";
}