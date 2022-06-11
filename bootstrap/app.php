<?php

/*
 |--------------------------------------------------------------------
 | Crea la aplicación: Variables de entorno, conexión a base de datos
 | y demás los componente necesarios para exponer los servicios REST
 |--------------------------------------------------------------------
 */

date_default_timezone_set('America/Lima');

$baseDir = __DIR__ . '/../';
$dotenv = Dotenv\Dotenv::createImmutable($baseDir);
$envFile = $baseDir . '.env';
if (file_exists($envFile)) {
    $dotenv->load();
}
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_DRIVER']);
$settings = require __DIR__ . '/../config/settings.php';
$app = new \Slim\App($settings);
$app->add(new \CorsSlim\CorsSlim());
$container = $app->getContainer();

//Cors

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header('Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS, DELETE');
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

require __DIR__ . '/../src/constants.php';

require __DIR__ . '/../config/dependencies.php';
require __DIR__ . '/../config/services.php';
require __DIR__ . '/../config/repositories.php';
require __DIR__ . '/../config/routes.php';