<?php
/**
 * handle unit and functional testing
 */

namespace Graviton\TestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use Liip\FunctionalTestBundle\LiipFunctionalTestBundle;

/**
 * GravitonTestBundle
 *
 * @category GravitonTestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonTestBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * set up a bare bones symfony2 context
     *
     * @return Array
     */
    public function getBundles()
    {
        return array(
            new LiipFunctionalTestBundle(),
        );
    }
}
