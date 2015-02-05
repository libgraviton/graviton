<?php

namespace Graviton\SwaggerBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonSwaggerBundle
 *
 * @category GravitonSwaggerBundle
 * @package  Graviton
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonSwaggerBundle extends Bundle
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
