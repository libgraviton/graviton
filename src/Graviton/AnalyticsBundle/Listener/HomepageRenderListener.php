<?php
/**
 * adds analytics links to the homepage
 */
namespace Graviton\AnalyticsBundle\Listener;

use Graviton\AnalyticsBundle\Request\ParamConverter\ServiceConverter;
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
     * @var ServiceConverter
     */
    private $serviceConter;

    /**
     * HomepageRenderListener constructor.
     *
     * @param ServiceConverter $serviceConverter service converter
     */
    public function __construct(ServiceConverter $serviceConverter)
    {
        $this->serviceConter = $serviceConverter;
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
        $services = $this->serviceConter->getServices();
        foreach ($services as $service) {
            $event->addRoute($service['$ref'], $service['profile']);
        }
    }
}
