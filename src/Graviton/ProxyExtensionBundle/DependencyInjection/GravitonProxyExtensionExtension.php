<?php
/**
 * ProxyExtensionBundle configuration
 */

namespace Graviton\ProxyExtensionBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class GravitonProxyExtensionExtension extends GravitonBundleExtension
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
     * Loads current configuration.
     *
     * @param array            $configs   Set of configuration options
     * @param ContainerBuilder $container Instance of the SF2 container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);
    }
}
