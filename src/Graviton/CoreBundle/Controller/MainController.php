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
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
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

        $router = $this->container->get('router');

        // get options routes since theyt are a good indicator if a services exists
        $optionRoutes = array_filter(
            $router->getRouteCollection()->all(),
            function ($route) {
                if ($route->getRequirement('_method') != 'OPTIONS') {
                    return false;
                }

                return is_null($route->getRequirement('id'));
            }
        );

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

        return $response;
    }
}
