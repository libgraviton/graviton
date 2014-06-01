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
 * @category Graviton
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
        return __DIR__.'/../Resources/config';
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

        if (array_key_exists('VCAP_SERVICES', $_ENV)) {
            $services = json_decode($_ENV['VCAP_SERVICES'], true);
            $mongo = $services['mongodb-2.2'][0]['credentials'];

            $container->setParameter('mongodb.default.server.uri', $mongo['url']);
            $container->setParameter('mongodb.default.server.db', $mongo['db']);
        }
    }
}
