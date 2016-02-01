<?php
/**
 * Controller for user/whoami endpoint
 */

namespace Graviton\SecurityBundle\Controller;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\SchemaBundle\SchemaUtils;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class WhoAmIController extends RestController
{
    /**
     * @param Response           $response    Response
     * @param RestUtilsInterface $restUtils   Rest utils
     * @param Router             $router      Router
     * @param ValidatorInterface $validator   Validator
     * @param EngineInterface    $templating  Templating
     * @param FormFactory        $formFactory form factory
     * @param DocumentType       $formType    generic form
     * @param ContainerInterface $container   Container
     * @param SchemaUtils        $schemaUtils schema utils
     */
    public function __construct(
        Response $response,
        RestUtilsInterface $restUtils,
        Router $router,
        ValidatorInterface $validator,
        EngineInterface $templating,
        FormFactory $formFactory,
        DocumentType $formType,
        ContainerInterface $container,
        SchemaUtils $schemaUtils
    ) {
        parent::__construct(
            $response,
            $restUtils,
            $router,
            $validator,
            $templating,
            $formFactory,
            $formType,
            $container,
            $schemaUtils
        );
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
            ['response' => $this->serialize($securityUser->getUser())],
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
