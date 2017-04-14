<?php

namespace Maelstromeous\Mariokart\ServiceProvider;

use Aura\Sql\ExtendedPdo;
use League\Container\ServiceProvider\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Database',
        'Aura\SqlQuery\QueryFactory'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->share('Database', function () {
            $config = $this->getContainer()->get('config')['database'];

            $pdo = new ExtendedPdo(
                "mysql:host={$config['host']};dbname={$config['name']}",
                $config['user'],
                $config['password']
            );

            return $pdo;
        });
    }
}
