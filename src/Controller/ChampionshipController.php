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
    public function newChampionship(ServerRequestInterface $request, ResponseInterface $response)
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
            $this->validateChampionshipPOST($json);
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

        $id = $this->executeInsertQuery($query);

        // Add the players and vehicles to the championship
        foreach ($json->players as $player) {
            $query = $this->newInsertQuery();
            $query->into('championships_players_vehicles');
            $query->cols([
                'player'       => $player->player,
                'championship' => $id,
                'vehicle'      => $player->vehicle
            ]);

            $this->executeInsertQuery($query);
        }

        // Return the championship ID to the view to redirect
        $response->getBody()->write(
            json_encode(['id' => $id])
        );
    }

    /**
     * Shows a singular championship
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  array                  $args     URI arguments
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function showChampionship(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        // Grab championship details
        $champData = $this->getChampionshipData($args['id']);

        $response->getBody()->write(
            $this->getTemplateDriver()->render(
                'championships/championship.html', ['data' => $champData]
            )
        );
    }

	/**
	 * Validates the POST request for creating a new championship
	 *
	 * @param  string $json [description]
	 *
	 * @return void
	 * @throws Maelstromeous\Mariokart\Exception\InvalidDataException
	 */
    private function validateChampionshipPOST($json)
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

    /**
     * Gets all the data to do with a championship
     *
     * @param  string $id The Championship ID
     *
     * @return array
     */
    private function getChampionshipData($id)
    {
        $data = [];

        // Get championship info
        $select = $this->newSelectQuery();
        $select->from('championships')
               ->cols(['*'])
               ->where('id = ?', $id)
               ->where('valid = ?', '1');

        $data['championship'] = $this->executeQuery($select);

        // @todo: Present this more nicely
        if (! $data['championship']) {
            die('Championship couldn\'t be found :\'(');
        }

        // Get stages & track info
        $select = $this->newSelectQuery();
        $select->from('stages AS s')
               ->cols(['s.*', 't.name'])
               ->join(
                   'INNER',
                   'tracks AS t',
                   't.id = s.track'
               )
               ->where('s.championship = ?', $id);

        $data['stages'] = $this->executeQuery($select, true);

        // Reindex the keys by stage #
        $data['stages'] = array_combine(range(1, count($data['stages'])), array_values($data['stages']));

        // For each stage, pull in positions if applicable
        if (count($data['stages']) > 0) {
            // Get stage IDs
            $ids = [];
            foreach ($data['stages'] as $stage) {
                $ids[] = $stage->id;
            }

            $in = '';
            foreach ($ids AS $i) {
                $in .= "'{$i}',";
            }
            $in = rtrim($in, ',');

            $select = $this->newSelectQuery();
            $select->from('stage_positions')
                   ->cols(['*'])
                   ->where("stage IN ($in)");

            $positions = $this->executeQuery($select, true);

            // Group the positions together by stage
            $sortedPositions = [];
            foreach ($positions as $key => $row) {
                $sortedPositions[$row->stage][] = $row;
            }

            // Attach the positions to each stage (saves doing multiple queries)
            foreach ($data['stages'] as $stage => $row) {
                if (count($sortedPositions[$stage])) {
                    $row->positions = $sortedPositions[$stage];
                } else {
                    $row->positions = null;
                }
            }

            // Get the player and vehicle information
            $select = $this->newSelectQuery();
            $select->from('championships_players_vehicles AS cpv')
                   ->cols(['cpv.*', 'p.name AS playerName'])
                   ->join(
                        'INNER',
                        'players AS p',
                        'cpv.player = p.id'
                    )
                    ->where('cpv.championship = ?', $id);

            $result = $this->executeQuery($select, true);

            // Sort players by their ID
            foreach ($result as $player) {
                $data['players'][$player->player] = $player;
            }
        }

        return $data;
    }
}
