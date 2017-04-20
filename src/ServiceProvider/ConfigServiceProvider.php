<?php

namespace Maelstromeous\Mariokart\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ConfigServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = ['config'];

    /**
     * @{inheritDoc}
     */
    public function register()
    {
        $this->getContainer()->share('config', function () {
            return [
                'environment' => $_ENV['ENVIRONMENT'],
                'base_url'    => $_ENV['BASE_URL'],
                'version'     => $_ENV['VERSION'],
                'database' => [
					'host' => $_ENV['DB_HOST'],
                    'port' => $_ENV['DB_PORT'],
                    'name' => $_ENV['DB_NAME'],
                    'user' => $_ENV['DB_USER'],
                    'pass' => $_ENV['DB_PASS']
                ]
            ];
        });
    }
}
