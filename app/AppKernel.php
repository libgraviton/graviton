<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Sensio\Bundle\DistributionBundle\SensioDistributionBundle;
use Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle;

/**
 * AppKernel
 *
 * @category Graviton
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AppKernel extends Kernel
{
    /**
     * bundle loader
     *
     * @var BundleLoader
     */
    protected $bundleLoader;

    /**
     * set bundle loader
     *
     * @param BundleLoader $bundleLoader bundle loader
     *
     * @return void
     */
    public function setBundleLoader(BundleLoader $bundleLoader)
    {
        $this->bundleLoader = $bundleLoader;
    }

    /**
     * {@inheritDoc}
     *
     * @return Array
     */
    public function registerBundles()
    {
        if ($this->bundleLoader) {
            $bundles = $this->bundleLoader->load();
        }
        // @todo move these into their own Bundles or remove completely
        $bundles[] = new SwiftmailerBundle();
        $bundles[] = new DoctrineBundle();
        $bundles[] = new TwigBundle();

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new SensioDistributionBundle();
            $bundles[] = new SensioGeneratorBundle();
        }

        return $bundles;
    }

    /**
     * load env configs with loader
     *
     * @param LoaderInterface $loader loader
     *
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.xml');
    }
}
