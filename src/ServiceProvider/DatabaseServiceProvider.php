<?php

namespace Maelstromeous\Mariokart\ServiceProvider;

use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use League\Container\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'Database',
        'Database\Data',
        'Database\Archive',
        'Aura\SqlQuery\QueryFactory'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->getContainer()->singleton('Database', function () {
            $config = $this->getContainer()->get('config')['database'];

            $pdo = new ExtendedPdo(
                "mysql:host={$config['host']};dbname={$config['schema']}",
                $config['user'],
                $config['password']
            );

            return $pdo;
        });

        $this->getContainer()->singleton('Database\Data', function () {
            $config = $this->getContainer()->get('config')['database_data'];

            $pdo = new ExtendedPdo(
                "mysql:host={$config['host']};dbname={$config['schema']}",
                $config['user'],
                $config['password']
            );

            return $pdo;
        });

        $this->getContainer()->singleton('Database\Archive', function () {
            $config = $this->getContainer()->get('config')['database_archive'];

            $pdo = new ExtendedPdo(
                "mysql:host={$config['host']};dbname={$config['schema']}",
                $config['user'],
                $config['password']
            );

            return $pdo;
        });

        $this->getContainer()->add('Aura\SqlQuery\QueryFactory', function () {
            return new QueryFactory('mysql');
        });
    }
}
