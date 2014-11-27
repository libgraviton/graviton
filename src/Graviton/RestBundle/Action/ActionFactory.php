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
            throw new \Exception("Action ".$actionName." (".$className.") not supported");
        }

        $action = new $className($request, $response);

        return $action;
    }
}
