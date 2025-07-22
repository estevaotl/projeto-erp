<?php

session_start();

require __DIR__ . '/../../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Api\Controllers\ProdutoController;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Rotas
$app->get('/', function ($request, $response, $args) {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../Views');
    $twig = new \Twig\Environment($loader);

    $html = $twig->render('home.twig');
    $response->getBody()->write($html);
    return $response;
});

$app->get('/produtos', [ProdutoController::class, 'obterTodos']);

$app->post('/produtos/salvar', [ProdutoController::class, 'salvar']);
$app->delete('/produtos/excluir/{id}', [ProdutoController::class, 'excluir']);

$app->run();