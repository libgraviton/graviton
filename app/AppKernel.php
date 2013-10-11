<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use FOS\RestBundle\FOSRestBundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\CoreBundle\GravitonCoreBundle;
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
     * {@inheritDoc}
     *
     * @return Array
     */
    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new MonologBundle(),
            new SwiftmailerBundle(),
            new DoctrineBundle(),
            new SensioFrameworkExtraBundle(),
            new JMSSerializerBundle(),
            new FOSRestBundle(),
            new GravitonCoreBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new SensioDistributionBundle();
            $bundles[] = new SensioGeneratorBundle();
        }

        $this->loadBundles($bundles);

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

    /**
     * load bundles from bundles
     *
     * This is the part of the GravitonBundleBundle that loads additional
     * bundles if a submodule implements GravitonBundleInterface and returns
     * bundle instances.
     *
     * @param array &$bundles new bundles get added here.
     *
     * @return void
     */
    private function loadBundles(&$bundles)
    {
        $newBundles = array();
        foreach ($bundles as $bundle) {
            if ($bundle instanceof GravitonBundleInterface) {
                $newBundles = array_merge($newBundles, $bundle->getBundles());
            }
        }
        $bundles = array_merge($bundles, $newBundles);
    }
}
