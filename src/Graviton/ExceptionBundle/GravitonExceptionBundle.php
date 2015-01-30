<?php
/**
 * handle exception output and logging
 */

namespace Graviton\ExceptionBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonExceptionBundle
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonExceptionBundle extends Bundle implements GravitonBundleInterface
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
