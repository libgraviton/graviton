<?php
/**
 * security user factory
 */

namespace Graviton\SecurityBundle\User\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ModelFactory
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ModelFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor of the class.
     *
     * @param ContainerInterface $container symfony container
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
        $service = $this->container->get('graviton.authentication.user_provider.model.noop');

        if (!empty($serviceId) && $this->container->has($serviceId)) {
            $service = $this->container->get($serviceId);
        }

        return $service;
    }
}
