<?php
/**
 * Controller for user/whoami endpoint
 */

namespace Graviton\SecurityBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use MongoDB\BSON\Regex;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WhoAmIController extends RestController
{

    /**
     * @var string
     */
    private $queryField;

    /**
     * set QueryField
     *
     * @param string $queryField queryField
     *
     * @return void
     */
    public function setQueryField($queryField)
    {
        $this->queryField = $queryField;
    }

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
        $username = $this->getSecurityUser()->getUserIdentifier();
        $document = $this->getModel()->getRepository()->findOneBy(
            [
                $this->queryField => new Regex('^'.preg_quote($username).'$', 'i')
            ]
        );

        /** @var Response $response */
        $response = $this->getResponse();
        $response->headers->set('Content-Type', 'application/json');

        $response->setStatusCode(Response::HTTP_OK);

        if (!$document) {
            $response->setContent(json_encode(new \stdClass()));
            return $response;
        }

        $response->setContent($this->restUtils->serialize($document));

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
