<?php

namespace Graviton\SecurityBundle\Tests;


use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GravitonSecurityBundleTestCase extends WebTestCase
{
    /**
     *
     *
     * @param array $options
     * @param array $server
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
}
