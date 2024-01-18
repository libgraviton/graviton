<?php
/**
 * monolog processor that adds a requestid to the record
 */

namespace Graviton\LogBundle\Monolog\Processor;

use Graviton\LogBundle\Listener\RequestIdListener;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RequestIdProcessor implements ProcessorInterface
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
     * make change
     *
     * @param LogRecord $record record
     *
     * @return LogRecord record
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            $record->extra['requestId'] = $this->requestStack->getCurrentRequest()->attributes->get(
                RequestIdListener::ATTRIBUTE_NAME,
                '????'
            );
        }

        return $record;
    }
}
