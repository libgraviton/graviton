<?php
/**
 * graviton test case
 */

namespace Graviton\TestBundle\Test;

use Graviton\AppKernel;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use lapistano\ProxyObject\ProxyBuilder;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Graviton test case
 *
 * Override creating a kernel with our custom bundle-bundle routine.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonTestCase extends WebTestCase
{
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
        $debug = true;

        $kernel = new AppKernel($env, $debug);
        $kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));

        //set error reporting for phpunit
        ini_set('error_reporting', E_ALL);

        return $kernel;
    }

    /**
     * Provides a proxy object of the provided class.
     *
     * @param string $class Namespaced name of the class to be proxied
     *
     * @return ProxyBuilder
     */
    public function getProxyBuilder($class)
    {
        return new ProxyBuilder($class);
    }

    /**
     * Provides a test double for the named calss.
     *
     * @param string $class   Full namespace of the class to be doubled
     * @param array  $methods List of methods to be doubled
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSimpleTestDouble($class, array $methods = array())
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
