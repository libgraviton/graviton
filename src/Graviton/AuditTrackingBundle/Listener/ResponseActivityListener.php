<?php
/**
 * Response listener to save activity
 */
namespace Graviton\AuditTrackingBundle\Listener;

use Graviton\AuditTrackingBundle\Manager\ActivityManager;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class ResponseActivityListener
 * @package Graviton\AuditTrackingBundle\Listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ResponseActivityListener
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
     * When response is prepared and ready to be sent.
     *
     * @param FilterResponseEvent $event Sf kernel response event
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->manager->registerResponseEvent($event->getResponse());
    }
}
