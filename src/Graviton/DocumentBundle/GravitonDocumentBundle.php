<?php
/**
 * integrate the mongodb flavour of the doctrine2-odm with graviton
 */

namespace Graviton\DocumentBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonDocumentBundle
 *
 * @category GravitonDocumentBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonDocumentBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array();
    }
}
