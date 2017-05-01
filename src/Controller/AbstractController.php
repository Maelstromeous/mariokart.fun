<?php

namespace Maelstromeous\Mariokart\Controller;

use Maelstromeous\Mariokart\Contract\DatabaseAwareInterface;
use Maelstromeous\Mariokart\Contract\DatabaseAwareTrait;
use Maelstromeous\Mariokart\Contract\TemplateAwareInterface;
use Maelstromeous\Mariokart\Contract\TemplateAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController implements
    DatabaseAwareInterface,
    TemplateAwareInterface
{
    use DatabaseAwareTrait;
    use TemplateAwareTrait;

    /**
     * Gets all tracks based off platform ID
     *
     * @var $platform integer Platform to filter by
     *
     * @return array
     */
    public function getTracks($platform)
    {
        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('tracks');
        $query->where('platform = ?', $platform);
        $query->orderBy(['name ASC']);

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $rows = $stm->fetchAll($pdo::FETCH_OBJ);

        $return = [];

        foreach ($rows as $track) {
            $return[$track->id] = $track->name;
        }

        return $return;
    }

    /**
     * Gets all vehicles based off platform ID
     *
     * @param  $platform integer
     *
     * @return array
     */
    public function getVehicles($platform)
    {
        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from("vehicles_{$platform}");
        $query->orderBy(['name ASC']);

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $rows = $stm->fetchAll($pdo::FETCH_ASSOC);

        $return = [];

        foreach ($rows as $vehicle) {
            $id = $vehicle['id'];
            unset($vehicle['id']);
            $return[$id] = $vehicle;
        }

        return $return;
    }

    /**
     * Gets all characters based off platform
     *
     * @param  integer $platform
     *
     * @return array
     */
    public function getCharacters($platform)
    {
        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('characters');
        $query->where('platform = ?', $platform);
        $query->orderBy(['name ASC']);

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $rows = $stm->fetchAll($pdo::FETCH_ASSOC);

        $return = [];

        foreach ($rows as $character) {
            $id = $character['id'];
            unset($character['id']);
            $return[$id] = $character;
        }

        return $return;
    }

    /**
     * Gets all players
     *
     * @todo FILTER by teams
     *
     * @return array
     */
    public function getPlayers()
    {
        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('players');
        $query->orderBy(['name ASC']);

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $rows = $stm->fetchAll($pdo::FETCH_ASSOC);

        $return = [];

        foreach ($rows as $player) {
            $return[$player['id']] = $player;
        }

        return $return;
    }

    /**
     * Gets all platforms for selection
     *
     * @return array
     */
    public function getPlatforms()
    {
        $pdo = $this->getDatabaseDriver();
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('platforms');
        $query->orderBy(['name ASC']);

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        $rows = $stm->fetchAll($pdo::FETCH_ASSOC);

        $return = [];

        foreach ($rows as $platform) {
            $return[$platform['id']] = $platform;
        }

        return $return;
    }

    /**
     * Gets the statistics bars for various platforms
     *
     * @return array
     */
    public function getStatbars($platform)
    {
        $statbars = [
            1 => [
                'speed'        => ['min' => 25, 'max' => 69],
                'weight'       => ['min' => 17, 'max' => 67],
                'acceleration' => ['min' => 16, 'max' => 67],
                'handling'     => ['min' => 18, 'max' => 67],
                'drift'        => ['min' => 17, 'max' => 67],
                'offroad'      => ['min' => 16, 'max' => 73],
                'miniturbo'    => ['min' => 16, 'max' => 67]
            ]
        ];

        return $statbars[$platform];
    }
}
