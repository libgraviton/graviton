<?php

namespace Graviton\ConsultationBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonPersonBundle
 *
 * @category Consultation
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonConsultationBundle extends Bundle implements GravitonBundleInterface
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
