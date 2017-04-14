<?php

namespace Maelstromeous\Mariokart\Controller;

use Maelstromeous\Mariokart\Contract\DatabaseAwareInterface;
use Maelstromeous\Mariokart\Contract\DatabaseAwareTrait;
use Maelstromeous\Mariokart\Contract\TemplateAwareInterface;
use Maelstromeous\Mariokart\Contract\TemplateAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MainController implements DatabaseAwareInterface, TemplateAwareInterface
{
    use DatabaseAwareTrait;
    use TemplateAwareTrait;

    /**
     * Landing
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $standings = $this->calculateStandings();
        $response->getBody()->write(
            $this->getTemplateDriver()->render('landing.html')
        );
    }

    /**
     * Calculates the standings required to show on the landing page
     *
     * @return array
     */
    private function calculateStandings()
    {
        $standings = [];

        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();

        var_dump($query);die;
    }
}
