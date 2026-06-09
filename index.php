<?php
session_start();

require_once 'app/helpers/SessionHelper.php';
require_once 'app/models/ProductModel.php';

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

if (isset($url[0]) && $url[0] === 'api') {
    $resource = $url[1] ?? '';
    $controllerName = ucfirst($resource) . 'ApiController';
    $controllerFile = 'app/controllers/' . $controllerName . '.php';

    header('Content-Type: application/json');

    if ($resource === '' || !file_exists($controllerFile)) {
        http_response_code(404);
        echo json_encode(['message' => 'Controller not found']);
        exit;
    }

    require_once $controllerFile;
    $controller = new $controllerName();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = $url[2] ?? null;

    switch ($method) {
        case 'GET':
            $action = $id ? 'show' : 'index';
            break;
        case 'POST':
            $action = 'store';
            break;
        case 'PUT':
            $action = $id ? 'update' : null;
            break;
        case 'DELETE':
            $action = $id ? 'destroy' : null;
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            exit;
    }

    if (!$action || !method_exists($controller, $action)) {
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        exit;
    }

    $id ? $controller->$action($id) : $controller->$action();
    exit;
}

$controllerName = isset($url[0]) && $url[0] !== ''
    ? ucfirst($url[0]) . 'Controller'
    : 'ProductController';

$action = isset($url[1]) && $url[1] !== ''
    ? $url[1]
    : 'index';

if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    die('Controller not found');
}

require_once 'app/controllers/' . $controllerName . '.php';

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die('Action not found');
}

call_user_func_array([$controller, $action], array_slice($url, 2));
?>
