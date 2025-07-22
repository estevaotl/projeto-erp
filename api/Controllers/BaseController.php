<?php

namespace Api\Controllers;

use Twig\Environment;
use Psr\Http\Message\ResponseInterface;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

abstract class BaseController {
    protected Environment $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../../app/Views');

        $this->twig = new Environment($loader, [
            'debug' => true
        ]);

        $this->twig->addExtension(new DebugExtension());
    }

    protected function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface {
        $html = $this->twig->render($template, $data);
        $response->getBody()->write($html);
        return $response;
    }
}
