<?php

namespace Graviton\TestBundle\Test;

use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * REST test case
 *
 * Contains additional helpers for testing RESTful servers
 *
 * @todo refactor alot (use overridden client and whatnot)
 *
 * @category GravitonTestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestTestCase extends GravitonTestCase
{
    /**
     * grab and decode reponse from client
     *
     * @param Object $client client
     *
     * @return Mixed
     *
     * @todo this needs to move into the client somehow
     */
    public function loadJsonFromClient($client)
    {
        return json_decode($client->getResponse()->getContent());
    }
}
