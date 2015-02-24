<?php
/**
 * controller for start page
 */

namespace Graviton\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Symfony\Component\Routing\Router;

/**
 * MainController
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class MainController implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    /**
     * create simple start page.
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function indexAction()
    {
        /** @var \Symfony\Component\Routing\Router $router */
        $router = $this->container->get('router');

        /** @var Response $response */
        $response = $this->container->get("graviton.rest.response");

        $mainPage = new \stdClass;
        $mainPage->message = 'Please look at the Link headers of this response for further information.';
        $mainPage->services = $this->determineServices(
            $router,
            $this->container->get('graviton.rest.restutils')->getOptionRoutes()
        );

        $response->setContent(json_encode($mainPage));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Link', $this->prepareLinkHeader($router));

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
     * @param Router $router
     *
     * @return string
     */
    protected function prepareLinkHeader(Router $router)
    {
        $links = new LinkHeader(array());
        $links->add(
            new LinkHeaderItem(
                $router->generate('graviton.core.rest.app.all', array(), true),
                array(
                    'rel'  => 'apps',
                    'type' => 'application/json'
                )
            )
        );

        return (string) $links;
    }

    /**
     * Determines what service endpoints are available.
     *
     * @param Router $router
     * @param array $optionRoutes
     *
     * @return array
     */
    protected function determineServices(Router $router, array $optionRoutes)
    {
        $sortArr = array();
        $services = array_map(
            function ($routeName) use ($router) {
                list($app, $bundle, $rest, $document) = explode('.', $routeName);
                $schemaRoute = implode('.', array($app, $bundle, $rest, $document, 'canonicalSchema'));

                return array(
                    '$ref'    => $router->generate($routeName, array(), true),
                    'profile' => $router->generate($schemaRoute, array(), true),
                );
            },
            array_keys($optionRoutes)
        );

        foreach ($services as $key => $val) {
            $sortArr[$key] = $val['$ref'];
        }

        array_multisort($sortArr, SORT_ASC, $services);

        return $services;
    }
}
