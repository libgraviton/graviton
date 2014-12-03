<?php
namespace Graviton\RestBundle\Action;

/**
 *  Action factory
 *
 *  There are some actions
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ActionFactory
{
    /**
     * Actions array
     *
     * @var array
     */

    private static $actions = array();
    /**
     * Constructor (private... use the factory method)
     *
     * @return void
     */
    private function __construct()
    {

    }

    /**
     * Factory method
     *
     * @param Request  $request  Request object
     * @param Response $response Response object
     *
     * @throws \Exception
     *
     * @return ActionInterface $action Aciton object
     */
    public static function factory($request, $response)
    {
        $routeParts = explode('.', $request->get('_route'));
        $actionName = end($routeParts);

        $className = __NAMESPACE__.'\\'.ucfirst($actionName)."Action";

        if (!class_exists($className)) {
            $msg = "Action %s (%s) not supported";
            throw new \Exception(sprintf($msg, $actionName, $className));
        }

        if (!isset(self::$actions[$request->get('_route')])) {
            $action = new $className($request, $response);
            self::$actions[$request->get('_route')] = $action;
        } else {
            $action = self::$actions[$request->get('_route')];
        }

        return $action;
    }
}
