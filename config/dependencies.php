<?php

use App\Handler\ApiError;
use App\QueryManager\Mysql\MysqlQueryManager;
use Psr\Container\ContainerInterface;
use \App\Exception\NotFound;

$container['mysqlQueryManager'] = static function (ContainerInterface $container) {
    $database = $container->get('settings')['db'];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;port=%s;charset=utf8',
        $database['host'],
        $database['name'],
        $database['port']
    );

    $mysqlQueryManager = new MysqlQueryManager($dsn, $database['user'], $database['pass']);
    $mysqlQueryManager->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysqlQueryManager->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $mysqlQueryManager->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $mysqlQueryManager;
};

$container['errorHandler'] = static function () {
    return new ApiError();
};

$container['notFoundHandler'] = static function () {
    return static function ($request, $response) {
        throw new NotFound('Ruta no encontrada.', 404);
    };
};
