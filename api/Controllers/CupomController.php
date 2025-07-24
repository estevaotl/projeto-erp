<?php

namespace Api\Controllers;

use Api\Controllers\BaseController;
use Api\Models\Cupom;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CupomController extends BaseController {
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $cupomModel = new Cupom();
        $cupons = $cupomModel->obterTodos();

        // render herdado da base
        return $this->render($response, 'cupons/home.twig', [
            'cupons' => $cupons,
            'pedido' => $_SESSION['pedido'] ?? null
        ]);
    }

    public function salvar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            $data = $request->getParsedBody();

            $referenciaCupom = $data['referencia'];
            $validadeCupom = $data['validade'];
            $valorMinimoCupom = $data['valorMinimo'];
            $valorDesconto = $data['valorDesconto'];

            if (empty($referenciaCupom)) {
                throw new Exception("Não é possivel cadastrar cupom sem enviar a referencia.");
            }

            $cupomModel = new Cupom();
            $cupomModel->create(
                array(
                    "referencia"    => $referenciaCupom,
                    "validade"      => (!empty($validadeCupom) ? $validadeCupom : NULL),
                    "valorMinimo"   => $valorMinimoCupom,
                    "valorDesconto" => $valorDesconto
                )
            );

            return $response->withHeader('Location', '/cupons')->withStatus(302);
        } catch (\Throwable $th) {
            $html = $this->twig->render('cupons/home.twig', ['erro' => $th->getMessage()]);
            $response->getBody()->write($html);
            return $response->withStatus(400);
        }
    }

    public function editar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();

            $cupomModel = new Cupom();
            $cupomExistente = array_shift($cupomModel->obterComRestricoes(array("id" => $id)));

            if (!$cupomExistente) {
                throw new Exception("Cupom com ID $id não encontrado.");
            }

            $camposParaAtualizar = array_filter([
                'id'          => $id,
                'referencia'  => $data['referencia'] ?? null,
                'validade'    => $data['validade'] ?? null,
                'valorMinimo' => $data['valorMinimo'] ?? null,
                'valorDesconto' => $data['valorDesconto'] ?? null
            ], fn($valor) => $valor !== null);

            $cupomModel->atualizar($camposParaAtualizar);

            return $response->withHeader('Location', '/cupons')->withStatus(302);
        } catch (\Throwable $th) {
            $html = $this->twig->render('cupons/home.twig', ['erro' => $th->getMessage()]);
            $response->getBody()->write($html);
            return $response->withStatus(400);
        }
    }

    public function excluir(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $idCupom = $args['id'];

        $cupomModel = new Cupom();
        $sucesso = $cupomModel->desativar($idCupom);

        $resultado = [
            'sucesso' => $sucesso,
            'mensagem' => $sucesso
                ? 'Cupom excluído com sucesso.'
                : 'Falha ao excluir cupom.'
        ];

        $response->getBody()->write(json_encode($resultado));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
