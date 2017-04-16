<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;

$router = new RouteCollection(
    isset($container) && $container instanceof ContainerInterface ? $container : new Container
);

$router->get('/', 'Maelstromeous\Mariokart\Controller\MainController::index');
$router->get('/championships/new', 'Maelstromeous\Mariokart\Controller\ChampionshipController::new');
$router->post('/championships/new', 'Maelstromeous\Mariokart\Controller\ChampionshipController::commitNewChampionship');

return $router;
