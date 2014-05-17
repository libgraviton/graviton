<?php

namespace Graviton\TestBundle\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Graviton\AppKernel;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\BundleBundle\GravitonBundleBundle;

class GravitonTestCase extends WebTestCase
{
    /**
     * return our namespaced AppKernel
     *
     * Most symfony projects keep their AppKernel class in phps
     * global scope. Since we don't this needs to be overridden.
     *
     * This also allows us to properly set up the BundleLoader
     * infrastucture. This isn't in the DIC since it is used to
     * bootstrap the DIC itself.
     *
     * @return \Graviton\AppKernel
     */
    public static function createKernel()
    {
        require_once __DIR__.'/../../../../app/AppKernel.php';

        $env = 'test';
        $debug = true;

        $kernel = new AppKernel($env, $debug);
        $kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));

        return $kernel;
    }
}
