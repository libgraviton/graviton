<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\CoreBundle\GravitonCoreBundle;
use Graviton\DocumentBundle\GravitonDocumentBundle;
use Graviton\FileBundle\GravitonFileBundle;
use Graviton\GeneratorBundle\GravitonGeneratorBundle;
use Graviton\MigrationBundle\GravitonMigrationBundle;
use Graviton\RestBundle\GravitonRestBundle;
use Graviton\SecurityBundle\GravitonSecurityBundle;
use League\FlysystemBundle\FlysystemBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */


class AppKernel extends Kernel
{

    use MicroKernelTrait;

    /**
     * project dir
     *
     * @var string
     */
    protected string $projectDir = __DIR__.'/../';

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }

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
                new GravitonDocumentBundle(),
                new GravitonRestBundle(),
                new GravitonGeneratorBundle(),
                new GravitonSecurityBundle(),
                new GravitonFileBundle(),
                new GravitonMigrationBundle()
            ]
        );

        $bundleLoader = new BundleLoader(new GravitonBundleBundle());
        return $bundleLoader->load($bundles);
    }
}
