<?php
/**
 * Controller for core/version endpoint
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VersionController extends RestController
{
    /**
     * @var array
     */
    private $versionInformation;

    /**
     * Build core utils
     * @param array $versionInformation version information
     * @return void
     */
    public function setVersionInformation(array $versionInformation)
    {
        $this->versionInformation = $versionInformation;
    }

    /**
     * Returns all version numbers
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function versionsAction()
    {
        $versions = [
            'versions' => $this->versionInformation
        ];

        $response = $this->getResponse()
                         ->setStatusCode(Response::HTTP_OK)
                         ->setContent(json_encode($versions));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns schema
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function versionsSchemaAction()
    {
        $response = $this->getResponse()
                         ->setStatusCode(Response::HTTP_OK)
                         ->setContent(json_encode($this->getModel()->getSchema()));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
