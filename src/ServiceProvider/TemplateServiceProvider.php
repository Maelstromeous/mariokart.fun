<?php

namespace Maelstromeous\Mariokart\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Maelstromeous\Mariokart\Contract\DatabaseAwareInterface;
use Maelstromeous\Mariokart\Contract\DatabaseAwareTrait;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;

class TemplateServiceProvider extends AbstractServiceProvider implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * @var array
     */
    protected $provides = [
        'Twig_Environment'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $config = $this->getContainer()->get('config');
        $version = $config['environment'] === 'production' ? $config ['version'] : date('U');
        $vehicles = $this->getVehicles();

        $statBars = [
            'speed'        => ['min' => 25, 'max' => 69],
            'weight'       => ['min' => 17, 'max' => 67],
            'acceleration' => ['min' => 16, 'max' => 67],
            'handling'     => ['min' => 18, 'max' => 67],
            'drift'        => ['min' => 17, 'max' => 67],
            'offroad'      => ['min' => 16, 'max' => 73],
            'miniturbo'    => ['min' => 16, 'max' => 67]
        ];

        $globals = [
            'asset_url'        => $config['base_url'] . '/assets',
            'base_url'         => $config['base_url'],
            'environment'      => $config['environment'],
            'version'          => "?v={$version}",
            'characters'       => $this->getCharacters(),
            'players'          => $this->getplayers(),
            'tracks'           => $this->getTracks(),
            'vehicles'         => $vehicles,
            'vehiclesJson'     => json_encode($vehicles),
            'statBars'         => $statBars,
            'statBarsJson'     => json_encode($statBars)
        ];

        $this->getContainer()->share('Twig_Environment', function () use ($globals, $config) {
            $loader = new Twig_Loader_Filesystem(__DIR__ . '/../../template');
            $twig   = new Twig_Environment($loader, [
                'cache' => $config['environment'] === 'production' ? __DIR__ . '/../../cache' : false,
                'debug' => $config['environment'] === 'production' ? false : true
            ]);

            // Add Globals
            foreach ($globals as $key => $val) {
                $twig->addGlobal($key, $val);
            }

            // Add current path
            $request = $this->getContainer()->get('Zend\Diactoros\ServerRequest');
            $twig->addGlobal('current_path', $request->getServerParams()['REQUEST_URI']);

            // Add extensions
            if ($config['environment'] !== 'production') {
                $twig->addExtension(new Twig_Extension_Debug);
            }

            return $twig;
        });
    }

    public function getTracks()
    {
        $pdo = $this->getContainer()->get('Database');
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('tracks');
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

    public function getVehicles()
    {
        $pdo = $this->getContainer()->get('Database');
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('vehicles');
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

    public function getCharacters()
    {
        $pdo = $this->getContainer()->get('Database');
        $query = $this->newSelectQuery();
        $query->cols(['*']);
        $query->from('characters');
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

    public function getplayers()
    {
        $pdo = $this->getContainer()->get('Database');
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
}
