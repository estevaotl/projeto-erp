<?php

namespace Api\Controllers;

use Api\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends BaseController {
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        // render herdado da base
        return $this->render($response, 'home.twig', [
            'pedido' => $_SESSION['pedido'] ?? null
        ]);
    }
}
