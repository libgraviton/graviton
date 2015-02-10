<?php
/**
 * GravitonRestBundle
 */

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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
