<?php

namespace Api\Controllers;

use Api\Models\ItemProduto;
use Api\Models\Produto;
use Api\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SacolaController extends BaseController {
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $produtoModel = new Produto();
        $produtos = $produtoModel->obterTodos();

        $itemProdutoModel = new ItemProduto();

        if ($produtos && count($produtos) > 0) {
            foreach ($produtos as &$produto) {
                $itensProduto = $itemProdutoModel->obterComRestricoes(array("idProduto" => $produto['id']));
                $produto['itens'] = $itensProduto;
            }
        }

        // render herdado da base
        return $this->render($response, 'produtos/index.twig', [
            'produtos' => $produtos,
            'pedido' => $_SESSION['pedido'] ?? null
        ]);
    }

    public function adicionarAoCarrinho(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = $request->getParsedBody();

        if (!isset($_SESSION['pedido'])) {
            $_SESSION['pedido'] = [
                'idPedido' => uniqid(),
                'produtos' => []
            ];
        }

        $idProduto  = $data['idProduto'];
        $idItem     = $data['idItem'];
        $referencia = $data['referencia'];
        $cor        = $data['cor'];
        $tamanho    = $data['tamanho'];
        $quantidade = (int) $data['quantidade'];
        $preco      = (float) $data['preco'];

        // Se o produto ainda não estiver na sacola
        if (!isset($_SESSION['pedido']['produtos'][$idProduto])) {
            $_SESSION['pedido']['produtos'][$idProduto] = [
                'nome' => '', // opcional, pode preencher se estiver disponível
                'itens' => []
            ];
        }

        $itemJaExiste = false;

        foreach ($_SESSION['pedido']['produtos'][$idProduto]['itens'] as &$item) {
            if ($item['idItem'] == $idItem) {
                $item['quantidade'] += $quantidade;
                $itemJaExiste = true;
                break;
            }
        }

        if (!$itemJaExiste) {
            $_SESSION['pedido']['produtos'][$idProduto]['itens'][] = [
                'idItem'     => $idItem,
                'referencia' => $referencia,
                'cor'        => $cor,
                'tamanho'    => $tamanho,
                'quantidade' => $quantidade,
                'preco'      => $preco
            ];
        }

        $response->getBody()->write(json_encode(['sucesso' => true]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function excluir(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $idProduto = $args['id'];

        $produtoModel = new Produto();
        $produtoDesativadoComSucesso = $produtoModel->desativar($idProduto);

        $itemProdutoModel = new ItemProduto();
        $itensProduto = $itemProdutoModel->obterComRestricoes(array("idProduto" => $idProduto));

        $possuiItensAtivos = !empty($itensProduto);
        $itensDesativadosComSucesso = true;

        if ($possuiItensAtivos) {
            $itensDesativadosComSucesso = $itemProdutoModel->desativar($idProduto);
        }

        $sucesso = $produtoDesativadoComSucesso && $itensDesativadosComSucesso;

        $resultado = [
            'sucesso' => $sucesso,
            'mensagem' => $sucesso
                ? 'Produto excluído com sucesso.'
                : 'Falha ao excluir produto.'
        ];

        $response->getBody()->write(json_encode($resultado));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
