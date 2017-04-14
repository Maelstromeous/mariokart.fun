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
        $response->getBody()->write(
            $this->getTemplateDriver()->render(
                'landing.html',
                [
                    'standings' => $this->getStandings()
                ]
            )
        );
    }

    /**
     * Calculates the standings required to show on the landing page
     *
     * @return array
     */
    private function getStandings()
    {
        $standings = [];

        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();

        $date = new \DateTime();
        $dateFrom = $date->format('Y-m-01 00:00:00');
        $dateTo = $date->format('Y-m-t 23:59:59');

        $sql = "SELECT
                	p.name AS `player`,
                    p.defaultchar,
                    COUNT(DISTINCT(rp.race)) AS races,
                    SUM(CASE WHEN rp.position = 1 THEN '1' ELSE '0' END) AS stage_wins,
                    ANY_VALUE(ch.wins) AS `champ_wins`
                FROM championships AS c
                INNER JOIN races AS r ON c.id = r.championship
                INNER JOIN races_positions AS rp ON r.id = rp.race
                INNER JOIN players AS p ON rp.player = p.id
                LEFT JOIN
                (
                	SELECT
                		COUNT(DISTINCT(id)) AS `wins`,
                        champion
                	FROM championships
                    GROUP BY id
                ) AS ch ON ch.champion = p.id
                WHERE valid = 1
                AND `date` BETWEEN '{$dateFrom}' AND '{$dateTo}'
                GROUP BY p.name, p.defaultchar";

        return $pdo->query($sql, $pdo::FETCH_OBJ)->fetchAll();
    }
}
