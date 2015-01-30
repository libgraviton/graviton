<?php

namespace Graviton\RestBundle;

use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use JMS\SerializerBundle\JMSSerializerBundle;
use Misd\GuzzleBundle\MisdGuzzleBundle;

/**
 * GravitonRestBundle
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonRestBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * set up basic bundles needed for being RESTful
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array(
            new MisdGuzzleBundle(),
            new JMSSerializerBundle(),
            new KnpPaginatorBundle(),
        );
    }
}
