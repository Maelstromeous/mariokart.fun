<?php

namespace Maelstromeous\Mariokart\Controller;

use Maelstromeous\Mariokart\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MainController extends AbstractController
{
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
                'landing/index.html',
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
                    dt.*,
                    SUM(dt.champ_wins / dt.championships) AS `champ_win_perc`,
                    SUM(dt.stage_wins / dt.stages) AS `stage_win_perc`,
                    ANY_VALUE
                    (
                        # If player hasn't played more than the total championships / total players - 10% then they're disqualified
                        CASE WHEN dt.championships > ((total_champs.count / total_players.count) * 0.9) THEN '1' ELSE '0' END
                    ) AS `qualifies`,
                    ANY_VALUE((total_champs.count / total_players.count) * 0.9) AS `qualification_limit`
                FROM
                (
                    SELECT
                        p.name AS `player`,
                        p.defaultchar,
                        COUNT(DISTINCT(c.id)) AS championships,
                        COUNT(DISTINCT(sp.stage)) AS stages,
                        SUM(CASE WHEN sp.position = 1 THEN '1' ELSE '0' END) AS stage_wins,
                        ANY_VALUE(ch.wins) AS `champ_wins`
                    FROM championships AS c
                    INNER JOIN stages AS s ON c.id = s.championship
                    INNER JOIN stage_positions AS sp ON s.id = sp.stage
                    INNER JOIN players AS p ON sp.player = p.id
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
                    GROUP BY p.name, p.defaultchar
                ) AS dt,
                (
                    # Get total number of championships to run qualitification checks
                    SELECT
                        COUNT(id) AS `count`
                    FROM championships
                ) AS `total_champs`,
                (
                    # Get the total number of participated players for qualification checks
                    SELECT
                        COUNT(DISTINCT(p.id)) AS `count`
                    FROM players AS p
                    INNER JOIN stage_positions AS sp ON sp.player = p.id
                ) AS `total_players`
                GROUP BY dt.player, dt.defaultchar
                ORDER BY champ_win_perc DESC, stage_win_perc DESC, champ_wins DESC";
        return $pdo->query($sql, $pdo::FETCH_OBJ)->fetchAll();
    }

    /**
     * Provides the players default character and vehicle for auto population
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getPlayerDefaults(ServerRequestInterface $request, ResponseInterface $response)
    {
        $post = json_decode($request->getBody()->getContents());
        $id = $post->id;

        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();
        $query->from('players');
        $query->cols([
            'defaultchar AS character',
            'defaultvehicle AS vehicle'
        ]);
        $query->where('id = ?', $id);

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $result = $stm->fetch($pdo::FETCH_OBJ);

        $response->getBody()->write(
            json_encode($result)
        );
    }
}
