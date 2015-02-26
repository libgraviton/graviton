<?php
/**
 * reformat exception into json
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * reformat exception into json so our REST server never returns html
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExceptionListener
{
    /**
     * {@inheritDoc}
     *
     * @param GetResponseForExceptionEvent $event prepraed response
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
    }

    /**
     * prepare a line of backtrace into a semi human readable json
     *
     * @param array $line Line to format
     *
     * @return array
     */
    private function prepareTraceLine($line)
    {
        $trace = array();
        if (array_key_exists('file', $line)) {
            $trace['file'] = new \stdClass;
            $trace['file']->path = $line['file'];
            $trace['file']->line = $line['line'];
        }

        if (array_key_exists('class', $line)) {
            $trace['call'] = $line['class'].$line['type'].$line['function'];
        } elseif (array_key_exists('function', $line)) {
            $trace['call'] = $line['function'];
        }

        $trace['args'] = array_map(
            function ($arg) {
                return $this->walkArg($arg);
            },
            $line['args']
        );

        return $trace;
    }

    /**
     * somehow mangle the the arguments of a trace into a jsonifiable form
     *
     * This is rather hacky (the whole class actually is). Since we won't be
     * using the exception walking part on prod anyway it can stay as is for
     * now.
     *
     * I would like to refactor this into something more nice later on but there
     * are some more important things that should be up and running first for
     * that to make sense.
     *
     * @param array $arg recursive array of arguments
     *
     * @return array
     */
    private function walkArg($arg)
    {
        if (is_array($arg)) {
            return array_map(
                function ($array) {
                    return $this->walkArg($array);
                },
                $arg
            );
        } elseif (is_object($arg)) {
            return 'instanceof '.get_class($arg);
        } else {
            return $arg;
        }
    }
}
