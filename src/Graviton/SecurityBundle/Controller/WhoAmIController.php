<?php
/**
 * Controller for user/whoami endpoint
 */

namespace Graviton\SecurityBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Graviton\SecurityBundle\Entities\AnonymousUser;
use MongoDB\BSON\Regex;
use Symfony\Component\HttpFoundation\Request;
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
            // if we don't have an actual object, we just return an object containing the query field and
            // anonymous
            $response->setContent(
                json_encode(
                    [
                        $this->queryField => AnonymousUser::USERNAME
                    ]
                )
            );
            return $response;
        }

        $response->setContent($this->restUtils->serialize($document));

        return $response;
    }

    /**
     * should return the current model schema
     *
     * @param Request $request request
     *
     * @return array the schema encoded
     */
    public function getModelSchema(Request $request) : array
    {
        $schema = parent::getModelSchema($request);
        $schema['info']['title'] = 'Whoami endpoint, returning the current identity.';

        $realPath = array_shift($schema['paths']);

        foreach ($realPath as $method => $body) {
            if ($method != 'get') {
                unset($realPath[$method]);
            }
        }

        $schema['paths'] = ['/person/whoami' => $realPath];

        return $schema;
    }
}
