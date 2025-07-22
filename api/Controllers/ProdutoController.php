<?php

namespace Api\Controllers;

use Api\Models\ItemProduto;
use Api\Models\Produto;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class ProdutoController {
    private Environment $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../../app/Views');

        $this->twig = new Environment($loader, [
            'debug' => true
        ]);

        $this->twig->addExtension(new DebugExtension());
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
        $data = $request->getParsedBody();

        $nomeProduto = $data['nomeProduto'];
        $referencias = $data['referencia'] ?? [];
        $precos = $data['preco'] ?? [];
        $estoques = $data['estoque'] ?? [];
        $tamanhos = $data['tamanho'] ?? [];
        $cores = $data['cor'] ?? [];

        $produtoModel = new Produto();
        $idProduto = $produtoModel->create($nomeProduto);

        $itemProdutoModel = new ItemProduto();

        $quantidadeItens = count($referencias);
        for ($i = 0; $i < $quantidadeItens; $i++) {
            $dadosItem = [
                'idProduto' => $idProduto,
                'itens' => [
                    'referencia' => $referencias[$i] ?? '',
                    'preco'      => $precos[$i] ?? 0,
                    'estoque'    => $estoques[$i] ?? 0,
                    'tamanho'    => $tamanhos[$i] ?? '',
                    'cor'        => $cores[$i] ?? ''
                ]
            ];
            $itemProdutoModel->create($dadosItem);
        }

        return $response->withHeader('Location', '/produtos')->withStatus(302);
    }

    public function excluir(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $idProduto = $args['id'];

        $produtoModel = new Produto();
        $produtoDesativado = $produtoModel->desativar($idProduto);

        $itemProdutoModel = new ItemProduto();
        $itensDesativados = $itemProdutoModel->desativar($idProduto);

        $resultado = [
            'sucesso' => $produtoDesativado && $itensDesativados,
            'mensagem' => ($produtoDesativado && $itensDesativados)
                ? 'Produto excluído com sucesso.'
                : 'Falha ao excluir produto.'
        ];

        $response->getBody()->write(json_encode($resultado));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
