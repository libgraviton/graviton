<?php
/**
 * adds analytics links to the homepage
 */
namespace Graviton\AnalyticsBundle\Listener;

use Graviton\AnalyticsBundle\Manager\ServiceManager;
use Graviton\CoreBundle\Event\HomepageRenderEvent;

/**
 * Class HomepageRenderListener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HomepageRenderListener
{

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * HomepageRenderListener constructor.
     *
     * @param ServiceManager $serviceManager service manager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Add our links to the homepage
     *
     * @param HomepageRenderEvent $event event
     *
     * @return void
     */
    public function onRender(HomepageRenderEvent $event)
    {
        $services = $this->serviceManager->getServices();
        foreach ($services as $service) {
            $event->addRoute($service['$ref'], $service['profile']);
        }
    }
}
