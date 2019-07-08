<?php
/**
 * monolog processor that adds a requestid to the record
 */

namespace Graviton\LogBundle\Monolog\Processor;

use Graviton\LogBundle\Listener\RequestIdListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RequestIdProcessor
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RequestIdProcessor constructor.
     *
     * @param RequestStack $requestStack request stack
     *
     * @return void
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * processes the record and adds a random request id
     *
     * @param array $record record
     *
     * @return array new record
     */
    public function processRecord(array $record)
    {
        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            $record['extra']['requestId'] = $this->requestStack->getCurrentRequest()->attributes->get(
                RequestIdListener::ATTRIBUTE_NAME,
                '????'
            );
        }

        return $record;
    }
}
