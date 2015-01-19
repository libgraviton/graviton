<?php

namespace Graviton\SecurityBundle\Listener;

use Graviton\SecurityBundle\EventListener\Strategies\StrategyCollection;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class AuthenticationListener
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AuthenticationListener
{
    /**
     * @var \Graviton\SecurityBundle\EventListener\Strategies\\StrategyCollection
     */
    private $strategies;


    /**
     * @param \Graviton\SecurityBundle\EventListener\Strategies\StrategyCollection $strategies
     */
    public function __construct(StrategyCollection $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * Callback run, when event was thrown.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $request = $event->getRequest();
            $request->attributes->add(
                $this->process($request)
            );
        }
    }

    /**
     * Extracts authentication information from the current request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function process(Request $request)
    {
        $results = array();

        /** @var \Graviton\SecurityBundle\EventListener\Strategies\StrategyInterface $strategy */
        foreach ($this->strategies as $strategy) {
            $results[$strategy->getId()] = $strategy->apply($request);
        }

        return $results;
    }
}
