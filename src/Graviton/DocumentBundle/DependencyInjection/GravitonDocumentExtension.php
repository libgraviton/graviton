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

        // grab mongo config directly from vcap...
        $services = getenv('VCAP_SERVICES');
        if (!empty($services)) {
            $services = json_decode($services, true);
            $mongo = $services['mongodb'][0]['credentials'];

            $container->setParameter('mongodb.default.server.uri', $mongo['uri']);
            $container->setParameter('mongodb.default.server.db', $mongo['database']);
        } else {
            $container->setParameter(
                'mongodb.default.server.uri',
                $container->getParameter('graviton.mongodb.default.server.uri')
            );
            $container->setParameter(
                'mongodb.default.server.db',
                $container->getParameter('graviton.mongodb.default.server.db')
            );
        }
    }
}
