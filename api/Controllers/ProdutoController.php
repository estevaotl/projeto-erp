<?php
namespace Api\Controllers;

use Api\Models\ItemProduto;
use Api\Models\Produto;
use Api\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ProdutoController {
    private Environment $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../../app/Views');
        $this->twig = new Environment($loader);
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

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $senha = $data['senha'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($senha) < 6) {
            $html = $this->twig->render('auth/login.twig', ['erro' => 'Dados inválidos']);
            $response->getBody()->write($html);
            return $response->withStatus(400);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user['senha'])) {
            $html = $this->twig->render('auth/login.twig', ['erro' => 'Credenciais inválidas']);
            $response->getBody()->write($html);
            return $response->withStatus(401);
        }

        $_SESSION['usuario'] = $user['id'];
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function showRegister(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $html = $this->twig->render('auth/register.twig');
        $response->getBody()->write($html);
        return $response;
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $senha = $data['senha'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            $html = $this->twig->render('auth/register.twig', ['erro' => 'Preencha corretamente os campos']);
            $response->getBody()->write($html);
            return $response->withStatus(400);
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $html = $this->twig->render('auth/register.twig', ['erro' => 'Email já cadastrado']);
            $response->getBody()->write($html);
            return $response->withStatus(409);
        }

        $userModel->create($email, $senha);
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
