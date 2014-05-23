<?php
/**
 * Load and manage bundle configuration.
 */

namespace Graviton\BundleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface
    as PrependInterface;

/**
 * GravitonBundleExtension
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonBundleExtension extends Extension implements PrependInterface
{
    /**
     * get path to bundles Resources/config dir
     *
     * @return String
     */
    public function getConfigDir()
    {
        return __DIR__.'/../Resources/config';
    }

    /**
     * {@inheritDoc}
     *
     * @param Array            $configs   configs to process
     * @param ContainerBuilder $container container to use
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader(
            $container,
            new FileLocator($this->getConfigDir())
        );
        $loader->load('services.xml');
    }

    /**
     * {@inheritDoc}
     *
     * Load additional config into the container.
     *
     * @param ContainerBuilder $container instance
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader(
            $container,
            new FileLocator($this->getConfigDir())
        );
        $loader->load('config.xml');
    }
}
