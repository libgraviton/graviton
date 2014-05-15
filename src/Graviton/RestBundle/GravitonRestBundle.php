<?php

namespace Graviton\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use JMS\SerializerBundle\JMSSerializerBundle;
use FOS\RestBundle\FOSRestBundle;

class GravitonRestBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * set up basic bundles needed for being RESTful
     *
     * @return Array
     */
    public function getBundles()
    {
        return array(
            new JMSSerializerBundle(),
            new FOSRestBundle(),
        );
    }
}
