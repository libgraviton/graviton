<?php
/**
 * Class XVersionResponseListener
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class XVersionResponseListener
{

    /** @var string */
    private $versionHeader;

    /**
     * Constructor
     *
     * @param string $versionHeader version header
     */
    public function __construct($versionHeader)
    {
        $this->versionHeader = $versionHeader;
    }

    /**
     * Adds a X-Version header to the response.
     *
     * @param ResponseEvent $event Current emitted event.
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $event->getResponse()->headers->set(
            'X-Version',
            $this->versionHeader
        );
    }
}
