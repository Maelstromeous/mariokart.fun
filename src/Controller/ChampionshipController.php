<?php

namespace Maelstromeous\Mariokart\Controller;

use Maelstromeous\Mariokart\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChampionshipController extends AbstractController
{
    /**
     * Show form to create a new championship
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function new(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response->getBody()->write(
            $this->getTemplateDriver()->render(
                'championships/new.html'
            )
        );
    }

    /**
     * AJAX Request which creates a new championship
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function commitNewChampionship(ServerRequestInterface $request, ResponseInterface $response)
    {
        $post = $request->getParsedBody();

        var_dump($post);
    }
}
