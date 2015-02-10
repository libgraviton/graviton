<?php
/**
 * manage and load bundle config.
 */

namespace Graviton\SecurityBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonSecurityExtension extends GravitonBundleExtension
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
     * Loads a specific configuration.
     *
     * @param array                                                   $configs   Set of configuration options
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container Parameter vault.
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['authentication_services'])) {
            $container->setParameter('graviton-security.authentication.services', $config['authentication_services']);
        }
    }
}
