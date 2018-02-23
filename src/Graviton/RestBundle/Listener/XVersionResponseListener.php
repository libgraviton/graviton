<?php
/**
 * Class XVersionResponseListener
 */

namespace Graviton\RestBundle\Listener;

use Graviton\CoreBundle\Service\CoreUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class XVersionResponseListener
{
    /** @var LoggerInterface */
    private $logger;

    /** @var \Graviton\CoreBundle\Service\CoreUtils */
    private $coreUtils;


    /**
     * Constructor
     *
     * @param CoreUtils       $coreUtils Instance of the CoreUtils class
     * @param LoggerInterface $logger    Instance of the logger class
     */
    public function __construct(CoreUtils $coreUtils, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->coreUtils = $coreUtils;
    }

    /**
     * Adds a X-Version header to the response.
     *
     * @param FilterResponseEvent $event Current emitted event.
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $event->getResponse();
        
        $response->headers->set(
            'X-Version',
            $this->coreUtils->getVersionInHeaderFormat()
        );
    }
}
