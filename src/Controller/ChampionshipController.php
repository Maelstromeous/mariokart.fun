<?php

namespace Maelstromeous\Mariokart\Controller;

use Maelstromeous\Mariokart\Controller\AbstractController;
use Maelstromeous\Mariokart\Exception\InvalidDataException;
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
        $json = json_decode($request->getBody()->getContents());

        // Validate that we have the info we need first

        try {
            $this->validatePOST($json);
        } catch (InvalidDataException $e) {
            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );
            return $response->withStatus(400);
        }

        // Valid. Now process.

        // Create new championship
        $query = $this->newInsertQuery();
        $query->into('championships');
        $query->cols([
            'date'  => date('Y-m-d H:i:s'),
            'valid' => 1
        ]);
        $pdo = $this->getDatabaseDriver();
        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $id = $pdo->lastInsertId($query->getLastInsertIdName('id'));

        // Add the players and vehicles to the championship
        foreach ($json->players as $player) {
            $query = $this->newInsertQuery();
            $query->into('championships_players_vehicles');
            $query->cols([
                'player'       => $player->player,
                'championship' => $id,
                'vehicle'      => $player->vehicle
            ]);

            $stm = $pdo->prepare($query->getStatement());
            $stm->execute($query->getBindValues());
        }

        // Return the championship ID to the view to redirect
        $response->getBody()->write(
            json_encode(['id' => $id])
        );
    }

    private function validatePOST($json)
    {
        foreach ($json->players as $player) {
            if (empty($player->character)) {
                throw new InvalidDataException('Missing Character');
            }

            if (empty($player->vehicle)) {
                throw new InvalidDataException('Missing Vehicle');
            }

            if (empty($player->player)) {
                throw new InvalidDataException('Missing Player ID');
            }
        }
    }
}
