<?php
/**
 * load services into di
 */

namespace Graviton\RabbitMqBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonRabbitMqExtension extends GravitonBundleExtension
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
     * Overwrite rabbitmq config from cloud if available
     *
     * @param ContainerBuilder $container instance
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        // populated from cf's VCAP_SERVICES variable
        $services = getenv('VCAP_SERVICES');
        if (!empty($services)) {
            $services = json_decode($services, true);

            if (!isset($services['rabbitmq-3.0'][0]['credentials'])) {
                return false;
            }

            $creds = $services['rabbitmq-3.0'][0]['credentials'];

            $container->setParameter('graviton.rabbitmq.host', $creds['host']);
            $container->setParameter('graviton.rabbitmq.port', $creds['port']);
            $container->setParameter('graviton.rabbitmq.user', $creds['username']);
            $container->setParameter('graviton.rabbitmq.password', $creds['password']);
            $container->setParameter('graviton.rabbitmq.vhost', $creds['vhost']);
        }
    }
}
