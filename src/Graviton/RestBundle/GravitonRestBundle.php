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
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
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
