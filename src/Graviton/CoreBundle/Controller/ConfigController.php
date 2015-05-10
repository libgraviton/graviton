<?php
/**
 * controller for app entities
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\ExceptionBundle\Exception\ValidationException;
use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * CoreController
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ConfigController extends RestController
{
    /**
     * Adds a new
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        $this->validateRecord($request->getContent());

        return parent::postAction($request);
    }

    /**
     * Changes the content of the entity identified by provided id.
     *
     * @param string  $id      Unique identifier of the entity to be altered.
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Graviton\ExceptionBundle\Exception\MalformedInputException
     */
    public function putAction($id, Request $request)
    {
        $this->validateRecord($request->getContent());

        return parent::putAction($id, $request);
    }
}
