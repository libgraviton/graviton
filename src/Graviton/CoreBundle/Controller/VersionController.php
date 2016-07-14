<?php
/**
 * Controller for core/version endpoint
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\CoreBundle\Service\CoreUtils;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\SchemaBundle\SchemaUtils;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionController extends RestController
{
    /**
     * @var CoreUtils
     */
    private $coreUtils;

    /**
     * @param Response           $response    Response
     * @param RestUtilsInterface $restUtils   Rest utils
     * @param Router             $router      Router
     * @param EngineInterface    $templating  Templating
     * @param ContainerInterface $container   Container
     * @param SchemaUtils        $schemaUtils schema utils
     * @param CoreUtils          $coreUtils   coreUtils
     */
    public function __construct(
        Response $response,
        RestUtilsInterface $restUtils,
        Router $router,
        EngineInterface $templating,
        ContainerInterface $container,
        SchemaUtils $schemaUtils,
        CoreUtils $coreUtils
    ) {
        parent::__construct(
            $response,
            $restUtils,
            $router,
            $templating,
            $container,
            $schemaUtils
        );
        $this->coreUtils = $coreUtils;
    }

    /**
     * Returns all version numbers
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function versionsAction()
    {
        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $versions = array();
        $versions['versions'] = $this->coreUtils->getVersion();
        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => json_encode($versions)],
            $response
        );
    }

    /**
     * Returns schema
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function versionsSchemaAction()
    {
        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $schema = $this->getModel()->getSchema();
        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => json_encode($schema)],
            $response
        );
    }
}
