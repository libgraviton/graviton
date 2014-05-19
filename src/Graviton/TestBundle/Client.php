<?php

namespace Graviton\TestBundle;

use Symfony\Bundle\FrameworkBundle\Client as FrameworkClient;

class Client extends FrameworkClient
{
    private $results;

    public function getResults()
    {
        return $this->results;
    }

    /**
     * prepare a deserialized copy of a json response
     *
     * @param object $response Response containing our return value as raw json
     *
     * @return object response
     *
     * @todo use JMSSerializer for additional JSON validation
     */
    protected function filterResponse($response)
    {
        $this->results = json_decode($response->getContent());
        return parent::filterResponse($response);
    }
}
