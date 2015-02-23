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

/**
 * MainController
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
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
        $response = $this->container->get("graviton.rest.response");
        $response->setStatusCode(Response::HTTP_OK);
        $router = $this->container->get('router');

        $links = LinkHeader::fromString('');
        $links->add(
            new LinkHeaderItem(
                $router->generate('graviton.core.rest.app.all', array(), true),
                array(
                    'rel' => 'apps',
                    'type' => 'application/json'
                )
            )
        );

        $response->headers->set('Link', (string) $links);

        # @todo don't find the composer file like so, use packagist to find and parse it if possible
        $composerFile = __DIR__.'/../../../../composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $response->headers->set('X-Version', $composer['version']);

        $mainPage = new \stdClass;
        $mainPage->message = 'Please look at the Link headers of this response for further information.';
        $mainPage->services = array();

        $restUtils = $this->container->get('graviton.rest.restutils');
        $optionRoutes = $restUtils->getOptionRoutes();

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

        $sortArr = array();
        foreach ($services as $key => $val) {
            $sortArr[$key] = $val['$ref'];
        }

        array_multisort($sortArr, SORT_ASC, $services);

        $mainPage->services = array_values($services);

        $response->setContent(json_encode($mainPage));

        // todo: make sure, that the correct content type is set.
        // todo: this should be covered by a kernel.response event listener?
        $response->headers->set('Content-Type', 'application/json');

        return $response;

        //todo:  use this in case the view layer does work properly again ;)
//        return $this->render(
//            'GravitonCoreBundle:Main:index.json.twig',
//            array('response' => $response->getContent()),
//            $response
//        );
    }
}
