<?php
/**
 * adds ProxyApi links to the homepage
 */
namespace Graviton\ProxyApiBundle\Listener;

use Graviton\ProxyApiBundle\Manager\ServiceManager;
use Graviton\CoreBundle\Event\HomepageRenderEvent;

/**
 * Class HomepageRenderListener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        //$services = $this->serviceManager->getServices();
        $services = [
            ['$ref'=>'ref', 'profile' => 'jacob']
        ];
        foreach ($services as $service) {
            $event->addRoute($service['$ref'], $service['profile']);
        }
    }
}
