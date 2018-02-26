<?php
/**
 * Controller for user/whoami endpoint
 */

namespace Graviton\SecurityBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

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
            return $this->render(
                'GravitonRestBundle:Main:index.json.twig',
                ['response' => json_encode(['Security is not enabled'])],
                $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED)
            );
        }

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => $this->restUtils->serialize($securityUser->getUser())],
            $response
        );
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

        $schema = $this->getModel()->getSchema();
        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => json_encode($schema)],
            $response
        );
    }
}
