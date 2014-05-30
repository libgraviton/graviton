<?php

namespace Graviton\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use JMS\SerializerBundle\JMSSerializerBundle;
use FOS\RestBundle\FOSRestBundle;
use Misd\GuzzleBundle\MisdGuzzleBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;

/**
 * GravitonRestBundle
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
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
            new MisdGuzzleBundle(),
            new KnpPaginatorBundle(),
        );
    }
}
