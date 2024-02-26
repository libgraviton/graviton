<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\CacheBundle\GravitonCacheBundle;
use Graviton\CoreBundle\GravitonCoreBundle;
use Graviton\DocumentBundle\GravitonDocumentBundle;
use Graviton\ExceptionBundle\GravitonExceptionBundle;
use Graviton\FileBundle\GravitonFileBundle;
use Graviton\GeneratorBundle\GravitonGeneratorBundle;
use Graviton\I18nBundle\GravitonI18nBundle;
use Graviton\LogBundle\GravitonLogBundle;
use Graviton\MigrationBundle\GravitonMigrationBundle;
use Graviton\RabbitMqBundle\GravitonRabbitMqBundle;
use Graviton\RestBundle\GravitonRestBundle;
use Graviton\SecurityBundle\GravitonSecurityBundle;
use League\FlysystemBundle\FlysystemBundle;
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
     * project dir
     *
     * @var string
     */
    protected string $projectDir = __DIR__.'/../';

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
     * {@inheritDoc}
     *
     * @return array bundles
     */
    public function registerBundles(): iterable
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Graviton\RqlParserBundle\GravitonRqlParserBundle(),
            new FlysystemBundle(),
            new \Graviton\JsonSchemaBundle\GravitonJsonSchemaBundle(),
            new \Graviton\AnalyticsBundle\GravitonAnalyticsBundle(),
            new \Graviton\CommonBundle\GravitonCommonBundle(),
            new \Sentry\SentryBundle\SentryBundle()
        ];

        $nonProdEnv = ($this->getEnvironment() == 'dev' || str_contains($this->getEnvironment(), 'test'));

        if ($nonProdEnv) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        if (class_exists('Graviton\TestServicesBundle\GravitonTestServicesBundle')) {
            $bundles[] = new \Graviton\TestServicesBundle\GravitonTestServicesBundle();
        }

        // our own bundles!
        $bundles = array_merge(
            $bundles,
            [
                new GravitonCoreBundle(),
                new GravitonExceptionBundle(),
                new GravitonDocumentBundle(),
                new GravitonRestBundle(),
                new GravitonI18nBundle(),
                new GravitonGeneratorBundle(),
                new GravitonCacheBundle(),
                new GravitonLogBundle(),
                new GravitonSecurityBundle(),
                new GravitonFileBundle(),
                new GravitonRabbitMqBundle(),
                new GravitonMigrationBundle()
            ]
        );

        $bundleLoader = new BundleLoader(new GravitonBundleBundle());
        return $bundleLoader->load($bundles);
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
