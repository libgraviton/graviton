<?php
/**
 * Controller for /favicon.ico
 */

namespace Graviton\CoreBundle\Controller;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FaviconController
{

    /**
     * renders a favicon
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function iconAction()
    {
        header('Content-Type: image/x-icon');
        // open our file
        $fp = fopen(__DIR__.'/../Resources/assets/favicon.ico', 'r');
        fpassthru($fp);
        fclose($fp);
        exit;
    }
}
