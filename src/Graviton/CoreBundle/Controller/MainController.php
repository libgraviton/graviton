<?php
/**
 * controller for start page
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\ProxyBundle\Service\ApiDefinitionLoader;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

/**
 * MainController
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class MainController
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var RestUtilsInterface
     */
    private $restUtils;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var ApiDefinitionLoader
     */
    private $apiLoader;
    
    /**
     * @var array
     */
    private $addditionalRoutes;

    /**
     * @var array
     */
    private $pathWhitelist;

    /**
     * @var array
     */
    private $proxySourceConfiguration;

    /**
     * @param Router              $router                   router
     * @param Response            $response                 prepared response
     * @param RestUtilsInterface  $restUtils                rest-utils from GravitonRestBundle
     * @param EngineInterface     $templating               templating-engine
     * @param ApiDefinitionLoader $apiLoader                loader for third party api definition
     * @param array               $additionalRoutes         custom routes
     * @param array               $pathWhitelist            serviec path that always get aded to the main page
     * @param array               $proxySourceConfiguration Set of sources to be recognized by the controller
     */
    public function __construct(
        Router $router,
        Response $response,
        RestUtilsInterface $restUtils,
        EngineInterface $templating,
        ApiDefinitionLoader $apiLoader,
        $additionalRoutes = array(),
        $pathWhitelist = [],
        array $proxySourceConfiguration = array()
    ) {
        $this->router = $router;
        $this->response = $response;
        $this->restUtils = $restUtils;
        $this->templating = $templating;
        $this->apiLoader = $apiLoader;
        $this->addditionalRoutes = $additionalRoutes;
        $this->pathWhitelist = $pathWhitelist;
        $this->proxySourceConfiguration = $proxySourceConfiguration;
    }

    /**
     * create simple start page.
     *
     * @return Response $response Response with result or error
     */
    public function indexAction()
    {
        $response = $this->response;

        $mainPage = new \stdClass();
        $mainPage->services = $this->determineServices(
            $this->restUtils->getOptionRoutes()
        );

        $mainPage->thirdparty = $this->registerThirdPartyServices();

        $response->setContent(json_encode($mainPage));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Link', $this->prepareLinkHeader());

        // todo: make sure, that the correct content type is set.
        // todo: this should be covered by a kernel.response event listener?
        $response->headers->set('Content-Type', 'application/json');

        return $this->render(
            'GravitonCoreBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }

    /**
     * Determines what service endpoints are available.
     *
     * @param array $optionRoutes List of routing options.
     *
     * @return array
     */
    protected function determineServices(array $optionRoutes)
    {
        $sortArr = array();
        $router = $this->router;
        foreach ($this->addditionalRoutes as $route) {
            // hack because only array keys are used
            $optionRoutes[$route] = null;
        }

        $services = array_map(
            function ($routeName) use ($router) {
                list($app, $bundle, $rest, $document) = explode('.', $routeName);
                $schemaRoute = implode('.', array($app, $bundle, $rest, $document, 'canonicalSchema'));

                return array(
                    '$ref' => $router->generate($routeName, array(), true),
                    'profile' => $router->generate($schemaRoute, array(), true),
                );
            },
            array_keys($optionRoutes)
        );

        foreach ($services as $key => $val) {
            if ($this->isRelevantForMainPage($val)) {
                $sortArr[$key] = $val['$ref'];
            } else {
                unset($services[$key]);
            }
        }
        array_multisort($sortArr, SORT_ASC, $services);

        return $services;
    }

    /**
     * Prepares the header field containing information about pagination.
     *
     * @return string
     */
    protected function prepareLinkHeader()
    {
        $links = new LinkHeader(array());
        $links->add(
            new LinkHeaderItem(
                $this->router->generate('graviton.core.rest.app.all', array (), true),
                array ('rel' => 'apps', 'type' => 'application/json')
            )
        );

        return (string) $links;
    }

    /**
     * tells if a service is relevant for the mainpage
     *
     * @param array $val value of service spec
     *
     * @return boolean
     */
    private function isRelevantForMainPage($val)
    {
        return (substr($val['$ref'], -1) === '/')
            || in_array(parse_url($val['$ref'], PHP_URL_PATH), $this->pathWhitelist);
    }

    /**
     * Resolves all third party routes and add schema info
     *
     * @param array $thirdApiRoutes list of all routes from an API
     *
     * @return array
     */
    protected function determineThirdPartyServices(array $thirdApiRoutes)
    {
        $definition = $this->apiLoader;
        $mainRoute = $this->router->generate(
            'graviton.core.static.main.all',
            array(),
            true
        );
        $services = array_map(
            function ($apiRoute) use ($mainRoute, $definition) {

                return array (
                    '$ref' => $mainRoute.$apiRoute,
                    'profile' => $mainRoute."schema/".$apiRoute."/item",
                );
            },
            $thirdApiRoutes
        );

        return $services;
    }

    /**
     * Finds configured external apis to be exposed via G2.
     *
     * @return array
     */
    private function registerThirdPartyServices()
    {
        $services = [];
        // getenv()... it's a workaround for run all tests on travis! will be removed!
        if (array_key_exists('swagger', $this->proxySourceConfiguration)
            && getenv('USER') !== 'travis'
            && getenv('HAS_JOSH_K_SEAL_OF_APPROVAL') !== true) {
            //@todo: this needs to be refactored in case there are other sources than swagger configuration files
            foreach ($this->proxySourceConfiguration['swagger'] as $thirdparty => $option) {
                $this->apiLoader->setOption($option);
                $services[$thirdparty] = $this->determineThirdPartyServices(
                    $this->apiLoader->getAllEndpoints(false, true)
                );
            }
        }

        return $services;
    }
}
