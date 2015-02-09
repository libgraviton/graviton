<?php

namespace Graviton\SecurityBundle\User\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ModelFactory
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ModelFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $serviceId;


    /**
     * Constructor of the class.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Determines what service to be used.
     *
     * @return \Graviton\RestBundle\Model\ModelInterface
     */
    public function create()
    {
        $serviceId = $this->container->getParameter('graviton.authentication.user_provider.model');
        $service =  $this->container->get('graviton.authentication.user_provider.model.noop');

        if (!empty($serviceId) && $this->container->has($serviceId)) {

            $service = $this->container->get($serviceId);
        }

        return $service;
    }
}
