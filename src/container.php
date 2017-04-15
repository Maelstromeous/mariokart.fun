<?php

use League\Container\Container;

$container = new Container();

$container->addServiceProvider(Maelstromeous\Mariokart\ServiceProvider\ConfigServiceProvider::class);
$container->addServiceProvider(Maelstromeous\Mariokart\ServiceProvider\DatabaseServiceProvider::class);
$container->addServiceProvider(Maelstromeous\Mariokart\ServiceProvider\HttpMessageServiceProvider::class);
$container->addServiceProvider(Maelstromeous\Mariokart\ServiceProvider\TemplateServiceProvider::class);

$container->inflector(Maelstromeous\Mariokart\Contract\ConfigAwareInterface::class)
          ->invokeMethod('setConfig', ['config']);
$container->inflector(Maelstromeous\Mariokart\Contract\DatabaseAwareInterface::class)
          ->invokeMethod('setDatabaseDriver', ['Database']);
$container->inflector(Maelstromeous\Mariokart\Contract\TemplateAwareInterface::class)
          ->invokeMethod('setTemplateDriver', ['Twig_Environment']);

$container->add(Maelstromeous\Mariokart\Controller\MainController::class);

return $container;
