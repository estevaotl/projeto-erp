<?php

namespace Api\Controllers;

use Api\Models\ItemProduto;
use Api\Models\Produto;
use Api\Controllers\BaseController;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProdutoController extends BaseController {
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
        return $this->render($response, 'produtos/home.twig', [
            'produtos' => $produtos,
            'pedido' => $_SESSION['pedido'] ?? null
        ]);
    }

    public function obterTodos(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $produtoModel = new Produto();
        $produtos = $produtoModel->obterTodos();

        $itemProdutoModel = new ItemProduto();

        if ($produtos && count($produtos) > 0) {
            foreach ($produtos as &$produto) {
                $itensProduto = $itemProdutoModel->obterComRestricoes(array("idProduto" => $produto['id']));
                $produto['itens'] = $itensProduto;
            }
        }

        $html = $this->twig->render('produtos/home.twig', ['produtos' => $produtos]);
        $response->getBody()->write($html);
        return $response;
    }

    public function salvar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            if (!$data || empty($data['nome']) || empty($data['itens'])) {
                throw new Exception("Dados inválidos");
            }

            $nomeProduto = trim($data['nome']);
            $itens = $data['itens'];

            if (count($itens) === 0) {
                throw new Exception("É necessário pelo menos um item vinculado ao produto.");
            }

            $produtoModel = new Produto();
            $idProduto = $produtoModel->create($nomeProduto);

            $itemProdutoModel = new ItemProduto();

            foreach ($itens as $item) {
                $dadosItem = [
                    'idProduto' => $idProduto,
                    'itens' => [
                        'referencia' => $item['referencia'] ?? '',
                        'preco'      => $item['preco'] ?? 0,
                        'estoque'    => $item['estoque'] ?? 0,
                        'tamanho'    => $item['tamanho'] ?? '',
                        'cor'        => $item['cor'] ?? ''
                    ]
                ];
                $itemProdutoModel->create($dadosItem);
            }

            $response->getBody()->write(json_encode([
                'sucesso' => true,
                'mensagem' => "Produto e seus itens cadastrados com sucesso."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $th) {
            $response->getBody()->write(json_encode([
                'sucesso' => false,
                'mensagem' => $th->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function editar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        try {
            $idProduto = (int) ($args['id'] ?? 0);

            if ($idProduto <= 0) {
                throw new Exception("ID do produto inválido.");
            }

            $data = json_decode($request->getBody()->getContents(), true);

            if (!$data || empty($data['nome']) || !isset($data['itens'])) {
                throw new Exception("Dados inválidos enviados.");
            }

            $produtoModel = new Produto();
            $produtoModel->update($idProduto, $data['nome']);

            $itemProdutoModel = new ItemProduto();

            foreach ($data['itens'] as $item) {
                $idItem = $item['idItem'];

                $dadosItem = [
                    'referencia' => $item['referencia'] ?? '',
                    'preco'      => $item['preco'] ?? 0,
                    'estoque'    => $item['estoque'] ?? 0,
                    'tamanho'    => $item['tamanho'] ?? '',
                    'cor'        => $item['cor'] ?? '',
                ];

                $itemProdutoModel->update((int) $idItem, $dadosItem);
            }

            $response->getBody()->write(json_encode([
                'sucesso' => true,
                'mensagem' => "Produto atualizado com sucesso."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $th) {
            $response->getBody()->write(json_encode([
                'sucesso' => false,
                'mensagem' => $th->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
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
