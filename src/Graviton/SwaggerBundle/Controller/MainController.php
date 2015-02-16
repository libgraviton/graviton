<?php
/**
 * Swagger controller.
 */

namespace Graviton\SwaggerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A controller that generates a swagger conform API specification.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
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
     * Return the swagger spec
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function indexAction()
    {
        $response = $this->container->get("graviton.rest.response");
        $apidocGenerator = $this->container->get("graviton.rest.apidoc");

        $response->setContent(json_encode($apidocGenerator->getSwaggerSpec()));

        return $response;
    }

    /**
     * Get the container object
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

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
}
