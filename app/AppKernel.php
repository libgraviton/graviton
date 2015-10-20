<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AppKernel extends Kernel
{
    /**
     * bundle loader
     *
     * @var \Graviton\BundleBundle\Loader\BundleLoader
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
        $configuredTimeZone = ini_get('date.timezone');
        if (empty($configuredTimeZone)) {
            date_default_timezone_set('UTC');
        }
        parent::__construct($environment, $debug);
    }

    /**
     * set bundle loader
     *
     * @param \Graviton\BundleBundle\Loader\BundleLoader $bundleLoader bundle loader
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
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new \Graviton\RqlParserBundle\GravitonRqlParserBundle(),
            new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new \Eo\AirbrakeBundle\EoAirbrakeBundle(),
            new \HadesArchitect\JsonSchemaBundle\JsonSchemaBundle(),
            new \Graviton\JsonSchemaBundle\GravitonJsonSchemaBundle(),
            new \OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test', 'oauth_dev'))) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new \Graviton\TestBundle\GravitonTestBundle();
        }

        if (strpos($this->getEnvironment(), 'oauth') !== false) {
            $bundles[] = new \HWI\Bundle\OAuthBundle\HWIOAuthBundle();
        }

        // autoload of Graviton specific bundles.
        if ($this->bundleLoader) {
            $bundles = $this->bundleLoader->load($bundles);
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
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
