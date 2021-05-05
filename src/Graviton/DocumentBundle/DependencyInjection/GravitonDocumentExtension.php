<?php
/**
 * manage and load bundle config.
 */

namespace Graviton\DocumentBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonDocumentExtension extends GravitonBundleExtension
{
    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getConfigDir()
    {
        return __DIR__ . '/../Resources/config';
    }

    /**
     * {@inheritDoc}
     *
     * Overwrite mongodb config from parent in cloud case.
     *
     * @param ContainerBuilder $container instance
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        $mongoDsn = $container->getParameter('graviton.mongodb.default.server.uri');
        $mongoDsnSecondary = $mongoDsn;

        if (str_contains($mongoDsn, 'replicaSet=')) {
            $mongoDsnSecondary = '&readPreference='
                .$container->getParameter('graviton.mongodb.secondary.read_preference');
        }

        $container->setParameter(
            'mongodb.default.server.uri',
            $mongoDsn
        );
        $container->setParameter(
            'mongodb.default.server.uri_secondary',
            $mongoDsnSecondary
        );

        $container->setParameter(
            'mongodb.default.server.db',
            $container->getParameter('graviton.mongodb.default.server.db')
        );
    }
}
