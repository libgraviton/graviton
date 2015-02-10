<?php
/**
 * AuthenticationListener
 *
 * PHP Version 5
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */

namespace Graviton\SecurityBundle\EventListener;

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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
final class AuthenticationListener
{
    /**
     * @var \Graviton\SecurityBundle\EventListener\Strategies\\StrategyCollection
     */
    private $strategies;


    /**
     * Constructor of the class.
     *
     * @param \Graviton\SecurityBundle\EventListener\Strategies\StrategyCollection $strategies how to handle headers.
     */
    public function __construct(StrategyCollection $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * Callback run, when event was thrown.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event Event thrown by kernel.
     *
     * @return void
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
     * @param \Symfony\Component\HttpFoundation\Request $request received Http request
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
