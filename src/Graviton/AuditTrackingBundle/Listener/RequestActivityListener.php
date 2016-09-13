<?php
/**
 * First request into graviton to be saved
 */
namespace Graviton\AuditTrackingBundle\Listener;

use Graviton\AuditTrackingBundle\Manager\ActivityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Class RequestActivityListener
 * @package Graviton\AuditTrackingBundle\Listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RequestActivityListener
{
    /** @var ActivityManager $manager */
    private $manager;

    /**
     * RequestActivityListener constructor.
     * @param ActivityManager $activityManager Business logic
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->manager = $activityManager;
    }

    /**
     * When request is received from user.
     *
     * @param GetResponseEvent $event Sf Event
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        $this->manager->registerRequestEvent($event->getRequest());
    }
}
