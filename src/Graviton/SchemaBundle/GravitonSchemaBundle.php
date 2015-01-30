<?php

namespace Graviton\SchemaBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonSchemaBundle
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonSchemaBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * return array of new bunde instances
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array();
    }

}
