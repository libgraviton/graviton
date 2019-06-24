<?php
/**
 * graviton test case
 */

namespace Graviton\TestBundle\Test;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Graviton\AppKernel;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Graviton test case
 *
 * Override creating a kernel with our custom bundle-bundle routine.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonTestCase extends WebTestCase
{

    use PrivateClassMethodTrait;
    use FixturesTrait;
    use ArraySubsetAsserts;

    /**
     * return our namespaced AppKernel
     *
     * Most symfony projects keep their AppKernel class in phps
     * global space. Since we don't this needs to be overridden.
     *
     * This also allows us to properly set up the BundleLoader
     * infrastucture. This isn't in the DIC since it is used to
     * bootstrap the DIC itself.
     *
     * @param array $options array of options, default: empty, currently ignored
     *
     * @return \Graviton\AppKernel
     */
    public static function createKernel(array $options = array())
    {
        include_once __DIR__ . '/../../../../app/AppKernel.php';

        $env = 'test';
        $debug = false;

        $kernel = new AppKernel($env, $debug);
        $kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));
        $kernel->boot();

        //set error reporting for phpunit
        ini_set('error_reporting', E_ALL);

        return $kernel;
    }

    /**
     * Provides a test double for the named calss.
     *
     * @param string $class   Full namespace of the class to be doubled
     * @param array  $methods List of methods to be doubled
     *
     * @return MockObject
     */
    public function getSimpleTestDouble($class, array $methods = array())
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * small wrapper for fixture loading
     *
     * @param array $classNames class names to load
     *
     * @return AbstractExecutor|null
     */
    public function loadFixturesLocal(array $classNames = [])
    {
        return $this->loadFixtures(
            $classNames,
            false,
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Builds up the environment to run the given command.
     *
     * @param string $name        name
     * @param array  $params      params
     * @param bool   $reuseKernel reuse kernel
     *
     * @return string command contents
     */
    protected function runCommand($name, array $params = [], $reuseKernel = false)
    {
        array_unshift($params, $name);
        if (!$reuseKernel) {
            if (null !== static::$kernel) {
                static::$kernel->shutdown();
            }
            $kernel = static::$kernel = $this->createKernel(['environment' => $this->environment]);
            $kernel->boot();
        } else {
            $kernel = $this->getContainer()->get('kernel');
        }
        $application = $this->createApplication($kernel);
        $input = new ArrayInput($params);
        $input->setInteractive(false);
        $fp = fopen('php://temp/maxmemory:'.$this->maxMemory, 'r+');
        $output = new StreamOutput($fp);
        $application->run($input, $output);
        rewind($fp);
        return stream_get_contents($fp);
    }

    /**
     * creates an application
     *
     * @param KernelInterface $kernel kernel
     *
     * @return Application
     */
    protected function createApplication(KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        return $application;
    }
}
