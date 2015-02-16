<?php
/**
 * WebTestCase for security tests
 */

namespace Graviton\SecurityBundle\Tests;

use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class GravitonSecurityBundleTestCase
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonSecurityBundleTestCase extends WebTestCase
{
    /**
     * Provides a HttpClient base on the Graviton\AppKernel
     *
     * @param array $options environment and debug option for kernel
     * @param array $server  server params
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        WebTestCase::ensureKernelShutdown();

        if (null === KernelTestCase::$class) {
            KernelTestCase::$class = '\\Graviton\\'.static::getKernelClass();
        }

        WebTestCase::$kernel = new WebTestCase::$class(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );

        WebTestCase::$kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));

        WebTestCase::$kernel->boot();

        $client = WebTestCase::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * Attempts to guess the kernel location.
     *
     * When the Kernel is located, the file is required.
     *
     * @return string The Kernel class name
     *
     * @throws \RuntimeException
     */
    protected static function getKernelClass()
    {
        // @codingStandardsIgnoreStart
        require_once __DIR__ . '/../../../../app/AppKernel.php';
        // @codingStandardsIgnoreEnd

        return 'AppKernel';
    }
}
