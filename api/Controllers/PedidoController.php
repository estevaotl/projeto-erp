<?php

namespace Api\Controllers;

use Api\Models\ItemProduto;
use Api\Models\Produto;
use Api\Controllers\BaseController;
use Api\Models\ItemPedido;
use Api\Models\Pedido;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PedidoController extends BaseController {
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $pedidoModel = new Pedido();
        $itemPedidoModel = new ItemPedido();
        $itemProdutoModel = new ItemProduto();
        $produtoModel = new Produto();

        $pedidos = $pedidoModel->obterTodos();

        foreach ($pedidos as &$pedido) {
            $itensPedido = $itemPedidoModel->obterComRestricoes(array("idPedido" => $pedido["id"]));

            if (!empty($itensPedido)) {
                foreach ($itensPedido as &$itemPedido) {
                    $item = array_shift($itemProdutoModel->obterComRestricoes(array("id" => $itemPedido["idItem"])));
                    if(empty($item))
                        continue;

                    $produto = array_shift($produtoModel->obterComRestricoes(array("id" => $item["idProduto"])));
                    if(empty($produto))
                        continue;

                    $infosItem = array(
                        "referencia"  => $item["referencia"],
                        "tamanho"     => $item["tamanho"],
                        "cor"         => $item["cor"],
                        "nomeProduto" => $produto["nome"]
                    );
                    $itemPedido["infosItem"] = $infosItem;
                }
            }

            $pedido["itensPedido"] = $itensPedido;
        }

        // render herdado da base
        return $this->render($response, 'pedidos/home.twig', [
            'pedidos' => $pedidos
        ]);
    }
}
