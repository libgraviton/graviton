<?php

namespace Graviton\TestBundle\Test;

use Graviton\TestBundle\Test\GravitonTestCase;

class RestTestCase extends GravitonTestCase
{
    // @todo this needs to move into the client somehow (i'll end up extending/replacing that?)
    public function loadJsonFromClient($client)
    {
        return json_decode($client->getResponse()->getContent());
    }
}
