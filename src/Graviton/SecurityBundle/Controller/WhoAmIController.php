<?php
/**
 * Controller for user/whoami endpoint
 */

namespace Graviton\SecurityBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WhoAmIController extends RestController
{

    /**
     * Currently authenticated user information.
     * If security is not enabled then header will be Not Allowed.
     * If User not found using correct header Anonymous user
     * Serialised Object transformer
     *
     * @return Response $response Response with result or error
     */
    public function whoAmIAction()
    {
        /** @var SecurityUser $securityUser */
        $securityUser = $this->getSecurityUser();

        /** @var Response $response */
        $response = $this->getResponse();
        $response->headers->set('Content-Type', 'application/json');

        if (!$securityUser) {
            $response->setContent(json_encode(['Security is not enabled']));
            $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
            return $response;
        }

        $response->setContent($this->restUtils->serialize($securityUser->getUser()));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * Returns schema
     *
     * @return Response $response Response with result or error
     */
    public function whoAmiSchemaAction()
    {
        /** @var Response $response */
        $response = $this->getResponse();
        $response->headers->set('Content-Type', 'application/json');

        $response->setContent(json_encode($this->getModel()->getSchema()));

        return $response;
    }
}
