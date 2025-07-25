<?php

session_start();

require __DIR__ . '/../../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Api\Controllers\IndexController;
use Api\Controllers\ProdutoController;
use Api\Controllers\CarrinhoController;
use Api\Controllers\CupomController;
use Api\Controllers\OpsController;
use Api\Controllers\PedidoController;
use Slim\Factory\AppFactory;

try {
    $app = AppFactory::create();
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    // Rotas
    $app->get('/', [IndexController::class, 'index']);

    $app->get('/produtos', [ProdutoController::class, 'index']);
    $app->post('/produtos/salvar', [ProdutoController::class, 'salvar']);
    $app->delete('/produtos/excluir/{id}', [ProdutoController::class, 'excluir']);
    $app->post('/produtos/editar/{id}', [ProdutoController::class, 'editar']);


    $app->get('/carrinho', [CarrinhoController::class, 'index']);
    $app->post('/carrinho/adicionar', [CarrinhoController::class, 'adicionarAoCarrinho']);
    $app->post('/carrinho/frete', [CarrinhoController::class, 'calcularFrete']);
    $app->post('/carrinho/finalizar', [CarrinhoController::class, 'finalizarPedido']);
    $app->get('/carrinho/retorno', [CarrinhoController::class, 'retorno']);


    $app->get('/cupons', [CupomController::class, 'index']);
    $app->post('/cupons/salvar', [CupomController::class, 'salvar']);
    $app->delete('/cupons/excluir/{id}', [CupomController::class, 'excluir']);
    $app->post('/cupons/editar/{id}', [CupomController::class, 'editar']);

    $app->get('/pedidos', [PedidoController::class, 'index']);
    $app->post('/pedido/webhook-atualizar-pedido-status', [PedidoController::class, 'webhookAtualizarStatus']);

    $app->get('/ops', [OpsController::class, 'index']);

    $app->run();
} catch (Throwable $e) {
    header('Location: /ops');
    exit;
}