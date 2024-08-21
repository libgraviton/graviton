<?php
/**
 * Graviton AppKernel
 */

namespace Graviton;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
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
     * configures container
     *
     * @param ContainerConfigurator $container container
     * @return void nothing
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
        } elseif (is_file($path = \dirname(__DIR__) . '/config/services.php')) {
            (include $path)($container->withPath($path), $this);
        }

        // parameters!
        if (is_file(\dirname(__DIR__) . '/config/parameters.yaml')) {
            $container->import('../config/parameters.yaml');
            $container->import('../config/{parameters}_' . $this->environment . '.yaml');
        }

        if (is_file($path = \dirname(__DIR__) . '/config/parameters_buildtime.php')) {
            (include $path)($container->withPath($path), $this);
        }

        if (is_file(\dirname(__DIR__) . '/config/parameters_runtime.yaml')) {
            $container->import('../config/parameters_runtime.yaml');
        }
    }

    /**
     * configures route
     *
     * @param RoutingConfigurator $routes routes
     * @return void nothing
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__) . '/config/routes.php')) {
            (include $path)($routes->withPath($path), $this);
        }

        // grv routes file?
        if (class_exists('GravitonDyn\EntityBundle\Entity\GravitonRoutes')) {
            foreach (GravitonDyn\EntityBundle\Entity\GravitonRoutes::getRoutes() as $route) {
                $routes->import($route);
            }
        }
    }
}
