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
            // Set xdebug settings if env is dev
            if ($_ENV['ENVIRONMENT'] === 'development') {
                ini_set('xdebug.var_display_max_depth', 10);
            }

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
