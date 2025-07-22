<?php

session_start();

require __DIR__ . '/../../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Api\Controllers\IndexController;
use Api\Controllers\ProdutoController;
use Api\Controllers\SacolaController;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Rotas
$app->get('/', [IndexController::class, 'index']);

$app->get('/produtos', [ProdutoController::class, 'index']);

$app->post('/produtos/salvar', [ProdutoController::class, 'salvar']);
$app->delete('/produtos/excluir/{id}', [ProdutoController::class, 'excluir']);

$app->post('/sacola/adicionar', [SacolaController::class, 'adicionarAoCarrinho']);

$app->run();