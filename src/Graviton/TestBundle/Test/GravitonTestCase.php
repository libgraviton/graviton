<?php

namespace Graviton\TestBundle\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Graviton\AppKernel;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\BundleBundle\GravitonBundleBundle;

/**
 * Graviton test case
 *
 * Override creating a kernel with our custom bundle-bundle routine.
 *
 * @category GravitonTestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
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
        include_once __DIR__.'/../../../../app/AppKernel.php';

        $env = 'test';
        $debug = true;

        $kernel = new AppKernel($env, $debug);
        $kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));

        //set error reporting for phpunit
        ini_set('error_reporting', E_ALL);

        return $kernel;
    }
}
