<?php
/**
 * Sf Extension for Audit Bundle
 */
namespace Graviton\AuditTrackingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonAuditTrackingExtension extends Extension
{
    /**
     * @param array            $configs   Data for configuration
     * @param ContainerBuilder $container Sf container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $environment = $container->getParameter("kernel.environment");

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Some configurations are turned on so tests are run with Tests user.
        if ($environment !== 'test') {
            $loader->load('parameters.yml');
        } else {
            $loader->load('parameters_dev.yml');
        }
    }
}
