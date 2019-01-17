<?php
/**
 * our own response class
 */
namespace Graviton\RestBundle\HttpFoundation;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{

    /**
     * Response constructor.
     *
     * @param string $content content
     * @param int    $status  status
     * @param array  $headers headers
     */
    public function __construct($content = '', $status = 200, $headers = [])
    {
        if (!isset($headers['content-type'])) {
            $headers['content-type'] = 'application/json; charset=UTF-8';
        }

        parent::__construct($content, $status, $headers);
    }
}
