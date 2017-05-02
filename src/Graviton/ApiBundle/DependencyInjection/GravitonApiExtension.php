<?php

namespace Graviton\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class GravitonApiExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        //$loader->load('parameters.yml');

        $uri = $container->getParameter('graviton.mongodb.default.server.uri');
        $dbc = $container->getParameter('graviton.mongodb.default.server.db');

        if ($services = getenv('VCAP_SERVICES')) {
            $services = json_decode($services, true);
            $mongo = $services['mongodb'][0]['credentials'];
            $uri = $mongo['uri'];
            $dbc = $mongo['database'];
        }

        $container->setParameter('graviton.api.mongodb.server.uri', $uri);
        $container->setParameter('graviton.api.mongodb.server.db', $dbc);
    }
}
