<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\TestBundle\GravitonTestBundle;
use Sensio\Bundle\DistributionBundle\SensioDistributionBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

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
     * {@inheritDoc}
     *
     * @param string $environment The environment
     * @param bool   $debug       Whether to enable debugging or not
     *
     * @return AppKernel
     */
    public function __construct($environment, $debug)
    {
        date_default_timezone_set('UTC');
        parent::__construct($environment, $debug);
    }

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
        $bundles = array(
            new FrameworkBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new DoctrineBundle(),
            new StofDoctrineExtensionsBundle(),
            new SensioFrameworkExtraBundle(),
            new SwiftmailerBundle(),
            new DoctrineMongoDBBundle(),
            new DoctrineFixturesBundle(),
        );

        if ($this->bundleLoader) {
            $bundles = array_merge($bundles, $this->bundleLoader->load());
        }

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new SensioDistributionBundle();
            $bundles[] = new SensioGeneratorBundle();
            $bundles[] = new GravitonTestBundle();
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
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.xml');
    }
}
