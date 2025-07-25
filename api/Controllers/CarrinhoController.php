<?php

namespace Api\Controllers;

use Api\Models\ItemProduto;
use Api\Models\Produto;
use Api\Controllers\BaseController;
use Api\Models\Cupom;
use Api\Models\ItemPedido;
use Api\Models\Pedido;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CarrinhoController extends BaseController {
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $cupomModel = new Cupom();
        $cupons = $cupomModel->obterTodos();

        // render herdado da base
        return $this->render($response, 'carrinho/home.twig', [
            'pedido' => $_SESSION['pedido'] ?? null,
            'cupons' => $cupons
        ]);
    }

    public function adicionarAoCarrinho(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = $request->getParsedBody();

        if (!isset($_SESSION['pedido'])) {
            $_SESSION['pedido'] = [
                'idPedido'    => uniqid(),
                'itensPedido' => []
            ];
        }

        $idItem     = $data['idItem'];
        $referencia = $data['referencia'];
        $cor        = $data['cor'];
        $tamanho    = $data['tamanho'];
        $quantidade = (int) $data['quantidade'];
        $preco      = (float) $data['preco'];

        $itemJaExiste = false;

        if (!isset($_SESSION['pedido']['itensPedido'])) {
            $_SESSION['pedido']['itensPedido'] = [];
        }

        foreach ($_SESSION['pedido']['itensPedido'] as &$itemPedido) {
            if ($itemPedido['idItem'] == $idItem) {
                $itemPedido['quantidade'] += $quantidade;
                $itemJaExiste = true;
                break;
            }
        }

        if (!$itemJaExiste) {
            $_SESSION['pedido']['itensPedido'][] = [
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

    public function calcularFrete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        if (!isset($_SESSION)) {
            session_start();
        }

        $subtotal = 0;

        if (isset($_SESSION['pedido']['itensPedido'])) {
            foreach ($_SESSION['pedido']['itensPedido'] as $itemPedido) {
                $subtotal += ((float) $itemPedido['preco'] * (int) $itemPedido['quantidade']);
            }
        }

        $frete = 20;
        if ($subtotal >= 52 && $subtotal <= 166.59) {
            $frete = 15;
        } elseif ($subtotal > 200) {
            $frete = 0;
        }

        $dados = [
            'subtotal' => number_format($subtotal, 2, ',', '.'),
            'frete' => number_format($frete, 2, ',', '.')
        ];

        $response->getBody()->write(json_encode($dados));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function finalizarPedido(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            if (!isset($_SESSION)) {
                session_start();
            }

            $data = json_decode($request->getBody()->getContents(), true);

            $cep = $data['cep'] ?? '';
            $emailCliente = $data['email'] ?? '';
            $idCupom = $data['cupom'] ?? '';

            if (empty($cep) || empty($emailCliente)) {
                throw new Exception('Dados incompletos.');
            }

            $viaCep = file_get_contents("https://viacep.com.br/ws/{$cep}/json");
            $endereco = json_decode($viaCep, true);

            if (isset($endereco['erro'])) {
                throw new Exception('CEP inválido.');
            }

            if (!isset($_SESSION['pedido']['itensPedido']) || empty($_SESSION['pedido']['itensPedido'])) {
                throw new Exception('Carrinho vazio.');
            }

            $valorDescontoCupom = 0;
            $cupomValido = false;
            if (isset($idCupom) && is_numeric($idCupom)) {
                $cupomModel = new Cupom();
                $cupom = $cupomModel->obterComRestricoes(array("id" => $idCupom));
                if(empty($cupom)) {
                    throw new Exception('Cupom selecionado inválido.');
                }

                $cupom = array_shift($cupom);
                $cupomValido = true;
                $valorDescontoCupom = $cupom["valorDesconto"];
            }

            $subTotal = 0;
            foreach ($_SESSION['pedido']['itensPedido'] as $itemPedido) {
                $subTotal += ((float) $itemPedido['preco'] * (int) $itemPedido['quantidade']);
            }

            if ($subTotal >= 52 && $subTotal <= 166.59) {
                $frete = 15.00;
            } elseif ($subTotal > 200) {
                $frete = 0.00;
            } else {
                $frete = 20.00;
            }

            $total = $subTotal + $frete - $valorDescontoCupom;

            $pedidoModel = new Pedido();
            $idPedido = $pedidoModel->criar(
                array(
                    "valorPedido" => $total,
                    "valorFrete"  => $frete,
                    "status"      => "ABERTO",
                    "idCupom"     => ($cupomValido ? $idCupom : NULL)
                )
            );

            $itemPedidoModel = new ItemPedido();
            foreach ($_SESSION['pedido']['itensPedido'] as $itemPedido) {
                $itemPedidoModel->criar(
                    array(
                        "idPedido"   => $idPedido,
                        "quantidade" => $itemPedido['quantidade'],
                        'idItem'     => $itemPedido['idItem']
                    )
                );
            }

            $resumo = [
                'idPedido'          => $idPedido,
                'subtotal'          => number_format($subTotal, 2, ',', '.'),
                'frete'             => number_format($frete, 2, ',', '.'),
                'total'             => number_format($total, 2, ',', '.'),
                'possuiCupom'       => $cupomValido,
                'descontoCupom'     => number_format($valorDescontoCupom, 2, ',', '.'),
                'cep'               => $cep,
                'email'             => $emailCliente,
                'enderecoFormatado' => "{$endereco['logradouro']}, {$endereco['bairro']}, {$endereco['localidade']} - {$endereco['uf']}"
            ];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp-relay.brevo.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'estevaotlnf@gmail.com';
                $mail->Password = 'vE4FS89nGwgOUAPV';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setLanguage("br");
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('estevaotlnf@gmail.com', 'Loja Exemplo');
                $mail->addAddress($emailCliente);
                $mail->Subject = 'Confirmação de Pedido';
                $mail->isHTML(true);
                $mail->Body = "
                    <h3>Pedido confirmado!</h3>" .
                    "<p><strong>Subtotal:</strong> R$ {$resumo['subtotal']}</p>" .
                    "<p><strong>Frete:</strong> R$ {$resumo['frete']}</p>" .
                    ($cupomValido ? "<p><strong>DescontoCupom:</strong> R$ {$resumo['descontoCupom']}</p>" : "") .
                    "<p><strong>Total:</strong> R$ {$resumo['total']}</p>" .
                    "<hr>" .
                    "<p>Entrega para: {$resumo['enderecoFormatado']}</p>
                ";

                $mail->send();
            } catch (Exception $e) {}

            $_SESSION['resumoPedido'] = $resumo;
            unset($_SESSION['pedido']);

            $response->getBody()->write(json_encode([
                'sucesso' => true
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

    public function retorno(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $html = $this->twig->render('carrinho/retorno.twig', ['resumo' => $_SESSION['resumoPedido'] ?? null]);
        $response->getBody()->write($html);
        return $response;
    }
}
