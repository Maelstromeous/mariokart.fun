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

        $globals = [
            'asset_url'        => $config['base_url'] . '/assets',
            'base_url'         => $config['base_url'],
            'environment'      => $config['environment'],
            'version'          => "?v={$version}"
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
}
