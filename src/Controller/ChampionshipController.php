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
        $vehicles = $this->getVehicles(1);
        $response->getBody()->write(
            $this->getTemplateDriver()->render(
                'championships/new.html',
                [
                    'characters'   => $this->getCharacters(1),
                    'platforms'    => $this->getPlatforms(),
                    'players'      => $this->getPlayers(),
                    'statbars'     => $this->getStatbars(1),
                    'statbarsJson' => json_encode($this->getStatbars(1)),
                    'vehicles'     => $vehicles,
                    'vehiclesJson' => json_encode($vehicles)
                ]
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
            'date'     => date('Y-m-d H:i:s'),
            'valid'    => 1,
            'platform' => $json->platform
        ]);

        $id = $this->executeInsertQuery($query);

        // Add the players and vehicles to the championship
        foreach ($json->players as $player) {
            $query = $this->newInsertQuery();
            $query->into('championships_players_vehicles');
            $query->cols([
                'player'       => $player->player,
                'championship' => $id,
                'character'    => $player->character,
                'vehicle'      => $player->vehicle,
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
        $data = $this->getChampionshipData($args['id']);

        $response->getBody()->write(
            $this->getTemplateDriver()->render(
                'championships/championship.html', [
                    'data' => $data
                ]
            )
        );
    }

    /**
     * Adds new stage to a championship
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  array                  $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function commitNewStage(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $json = json_decode($request->getBody()->getContents());

        // Validate that we have the info we need first
        try {
            $this->validateStagePOST($json, $args);
        } catch (InvalidDataException $e) {
            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );
            return $response->withStatus(400);
        }

        // Valid
        // Create new stage
        $query = $this->newInsertQuery();
        $query->into('stages');
        $query->cols([
            'championship' => $json->championship,
            'track'        => $json->track
        ]);

        $id = $this->executeInsertQuery($query);

        // Add the players positions to the stage
        foreach ($json->players as $player) {
            $query = $this->newInsertQuery();
            $query->into('stages_positions');
            $query->cols([
                'stage'    => $id,
                'player'   => $player->id,
                'position' => $player->pos
            ]);

            $this->executeInsertQuery($query);
        }

        $response->getBody()->write(
            json_encode(['success' => true])
        );
    }

    /**
     * Validates the POST request for creating a new championship
     *
     * @param  stdClass $json
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
     * Validates the POST request for creating a new stage
     *
     * @param  stdClass $json
     * @param  array    $args
     *
     * @return void
     * @throws Maelstromeous\Mariokart\Exception\InvalidDataException
     */
    private function validateStagePOST($json, array $args)
    {
        if (empty($json->championship)) {
            throw new InvalidDataException('Missing Championship ID');
        }

        if ($json->championship != $args['id']) {
            throw new InvalidDataException('POST championionship ID doesn\'t match route');
        }

        if (empty($json->track)) {
            throw new InvalidDataException('Missing Track ID');
        }

        foreach ($json->players as $player) {
            if (empty($player->id)) {
                throw new InvalidDataException('Missing Player ID');
            }

            if (empty($player->pos)) {
                throw new InvalidDataException('Missing Player Position');
            }
        }

        // Check if championship is still in progress
        $select = $this->newSelectQuery();
        $select->from('championships')
               ->cols(['*'])
               ->where('id = ?', $json->championship)
               ->where('valid = ?', 1);
        $championship = $this->executeQuery($select);

        if (! $championship) {
            throw new InvalidDataException('Championship couldn\'t be found!');
        }

        if ($championship->finished == 1) {
            throw new InvalidDataException('Championship is finished and can\'t be edited further');
        }

        // Validate the supplied track for the platform
        $select = $this->newSelectQuery();
        $select->from('tracks')
               ->cols(['*'])
               ->where('id = ?', $json->track)
               ->where('platform = ?', $championship->platform);
        $track = $this->executeQuery($select);

        if (! $track) {
            throw new InvalidDataException('Track couldn\'t be found!');
        }

        // Validate we don't already have the same track in the championship
        $select = $this->newSelectQuery();
        $select->from('stages')
               ->cols(['*'])
               ->where('championship = ?', $json->championship)
               ->where('track = ?', $json->track);
        $track = $this->executeQuery($select);

        if ($track) {
            throw new InvalidDataException("Duplicate track #{$json->track} detected for championship");
        }

        // Validate players exist in the championship
        $select = $this->newSelectQuery();
        $select->from('championships_players_vehicles')
               ->cols(['*'])
               ->where('championship = ?', $json->championship);
        $players = $this->executeQuery($select, true);

        $assigned = [];
        foreach ($json->players as $player) {
            $found = false;
            foreach ($players as $row) {
                if ($row->player === $player->id) {
                    $found = true;
                }
            }
            if ($found === false) {
                throw new InvalidDataException("Player {$player->id} was not found in the championship!");
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
        $data['championship']->tracksAssigned = [];

        // Get points system for platform so we can use to calculate player points later
        $data['points']     = $this->getPoints($data['championship']->platform);

        // @todo: Present this more nicely
        if (! $data['championship']) {
            die('Championship couldn\'t be found :\'(');
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
                ->where('cpv.championship = ?', $id)
                ->orderBy(['playerName ASC']);

        $result = $this->executeQuery($select, true);

        // Sort players by their ID
        foreach ($result as $player) {
            $data['players'][$player->player] = $player;
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

        // For each stage, pull in positions if applicable
        if (count($data['stages']) > 0) {
            // Reset to 1 indexed rather than 0
            $data['stages'] = array_combine(range(1, count($data['stages'])), array_values($data['stages']));

            // Get stage IDs
            $ids = [];
            foreach ($data['stages'] as $stage) {
                $ids[] = $stage->id;
                $data['championship']->tracksAssigned[] = $stage->track;
            }

            $in = '';
            foreach ($ids AS $i) {
                $in .= "'{$i}',";
            }
            $in = rtrim($in, ',');

            $select = $this->newSelectQuery();
            $select->from('stages_positions')
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
                if (count($sortedPositions[$row->id])) {
                    $row->positions = $sortedPositions[$row->id];
                } else {
                    $row->positions = null;
                }

                // Figure out player points
                foreach ($row->positions as $position) {
                    $playerPoints = $data['players'][$position->player];

                    if (! isset($playerPoints->points)) {
                        $playerPoints->points = 0;
                    }
                    $points = $data['points'][$position->position];
                    $current = $playerPoints->points;
                    $data['players'][$position->player]->points = $current + $points;
                }
            }
        }

        // Get platform specific information
        $data['characters'] = $this->getCharacters($data['championship']->platform);
        $data['tracks']     = $this->getTracks($data['championship']->platform);
        $data['vehicles']   = $this->getVehicles($data['championship']->platform);

        return $data;
    }
}
