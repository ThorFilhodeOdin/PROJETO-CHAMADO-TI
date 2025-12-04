<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
// headers JSON e CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/route/routes.php';
require_once __DIR__ . '/app/core/Database.php';

require_once __DIR__ . '/app/models/Chamados.php';
require_once __DIR__ . '/app/services/ChamadosService.php';
require_once __DIR__ . '/app/controllers/ChamadosController.php';
require_once __DIR__ . '/app/repositorys/repositoryChamados.php';

require_once __DIR__ . '/app/models/Users.php';
require_once __DIR__ . '/app/services/UserService.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/repositorys/repositoryUsuarios.php';


$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query_string = $_SERVER['QUERY_STRING'] ?? '';

$router = new routes();
$router->handle($method, $path, $query_string);
?>