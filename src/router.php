<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;

$router = new RouteCollection(
    isset($container) && $container instanceof ContainerInterface ? $container : new Container
);

$router->get('/', 'Maelstromeous\Mariokart\Controller\MainController::index');
$router->get('/championships/new', 'Maelstromeous\Mariokart\Controller\ChampionshipController::newChampionship');
$router->post('/championships/new', 'Maelstromeous\Mariokart\Controller\ChampionshipController::commitNewChampionship');
$router->get('/championships/{id}/races', 'Maelstromeous\Mariokart\Controller\ChampionshipController::races');
$router->post('/championships/{id}/addrace', 'Maelstromeous\Mariokart\Controller\ChampionshipController::commitNewRace');

$router->post('/misc/player-defaults', 'Maelstromeous\Mariokart\Controller\MainController::getPlayerDefaults');

return $router;
