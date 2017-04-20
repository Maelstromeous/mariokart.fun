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
$router->get('/championship/{id}', 'Maelstromeous\Mariokart\Controller\ChampionshipController::showChampionship');
$router->post('/championship/{id}/addStage', 'Maelstromeous\Mariokart\Controller\ChampionshipController::commitNewStage');

$router->post('/misc/player-defaults', 'Maelstromeous\Mariokart\Controller\MainController::getPlayerDefaults');

return $router;
