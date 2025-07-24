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
                    $itemBanco = $itemProdutoModel->obterComRestricoes(array("id" => $itemPedido["idItem"], "ativosSomente" => false));
                    if(empty($itemBanco))
                        continue;

                    $item = array_shift($itemBanco);

                    $produtoBanco = $produtoModel->obterComRestricoes(array("id" => $item["idProduto"], "ativosSomente" => false));
                    if(empty($produtoBanco))
                        continue;

                    $produto = array_shift($produtoBanco);

                    $infosItem = array(
                        "referencia"       => $item["referencia"],
                        "tamanho"          => $item["tamanho"],
                        "cor"              => $item["cor"],
                        "nomeProduto"      => $produto["nome"],
                        "itemEstaAtivo"    => filter_var($item["ativo"], FILTER_VALIDATE_BOOLEAN),
                        "produtoEstaAtivo" => filter_var($produto["ativo"], FILTER_VALIDATE_BOOLEAN),
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
