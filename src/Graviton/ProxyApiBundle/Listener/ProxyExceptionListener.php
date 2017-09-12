<?php
/**
 * ProxyExceptionListener
 */
namespace Graviton\ProxyApiBundle\Listener;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ProxyExceptionListener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProxyExceptionListener extends HttpException
{
    private $statusCode;

    /**
     * ProxyExceptionListener constructor.
     * @param int    $statusCode valid response status code
     * @param string $message    explain the reason
     * @param int    $code       default info code Bad Request
     */
    public function __construct(
        $statusCode,
        $message = null,
        $code = 400
    ) {
        $this->statusCode = empty($statusCode) ? 404 : $statusCode;
        $message = 'Proxy error: ' . $message;

        parent::__construct($statusCode, $message, null, [], $code);
    }
}
