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
readonly class XVersionResponseListener
{

    /**
     * Constructor
     *
     * @param string $versionHeader version header
     */
    public function __construct(private string $versionHeader)
    {
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
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }

        $event->getResponse()->headers->set(
            'X-Version',
            $this->versionHeader
        );
    }
}
