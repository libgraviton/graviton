<?php
/**
 * Load and manage bundle configuration.
 */

namespace Graviton\BundleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface as PrependInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * GravitonBundleExtension
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonBundleExtension extends Extension implements PrependInterface
{
    /**
     * {@inheritDoc}
     *
     * @param array            $configs   configs to process
     * @param ContainerBuilder $container container to use
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadFiles($this->getConfigDir(), $container, ['services.xml','services.yml']);
    }

    /**
     * get path to bundles Resources/config dir
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
     * Load additional config into the container.
     *
     * @param ContainerBuilder $container instance
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $this->loadFiles($this->getConfigDir(), $container, ['config.xml','config.yml','parameters.yml']);
    }

    /**
     * Returns extension configuration.
     *
     * @param array                                                   $config    An array of configuration values
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     *
     * @return \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return parent::getConfiguration($config, $container);
    }

    /**
     * Load config files, xml or yml.
     * If will only include the config file if it's in the allowed array.
     *
     * @param string           $dir       folder
     * @param ContainerBuilder $container Sf container
     * @param array            $allowed   array of files to load
     *
     * @return void
     */
    private function loadFiles($dir, ContainerBuilder $container, array $allowed)
    {
        $locator = new FileLocator($dir);
        $xmlLoader = new Loader\XmlFileLoader($container, $locator);
        $ymlLoader = new Loader\YamlFileLoader($container, $locator);

        $finder = new Finder();
        $finder->files()->in($dir);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if (!in_array($file->getFilename(), $allowed)) {
                continue;
            }
            if ('xml' == $file->getExtension()) {
                $xmlLoader->load($file->getRealPath());
            } elseif ('yml' == $file->getExtension()) {
                $ymlLoader->load($file->getRealPath());
            }
        }
    }
}
