<?php
namespace Graviton\RestBundle\Action;

use Symfony\Component\Routing\RouterInterface;

/**
 * Action Interface
 *
 * There is a class for every possible action (all,get,put...)
 * Feel free to add more functionality to this interface / classes.
 * At the moment, we use it to generate the right ref to this action
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface ActionInterface
{
    /**
     * All action
     *
     * @var string
     */
    const ACTION_ALL = "all";

    /**
     * Get action
     *
     * @var string
     */
    const ACTION_GET = "get";

    /**
     * Post action
     *
     * @var string
     */
    const ACTION_POST = "post";

    /**
     * Put action
     *
     * @var string
     */
    const ACTION_PUT = "put";

    /**
     * Patch action
     *
     * @var string
     */
    const ACTION_PATCH = "patch";

    /**
     * Delete action
     *
     * @var string
     */
    const ACTION_DELETE = "delete";

    /**
     * Schema action
     *
     * @var string
     */
    const ACTION_CANONICAL_SCHEMA = "canonicalSchema";

    /**
     * Schema id action
     *
     * @var string
     */
    const ACTION_CANONICAL_ID_SCHEMA = "canonicalIdSchema";

    /**
     * Options action
     *
     * @var string
     */
    const ACTION_OPTIONS = "options";

    /**
     * Id options action
     *
     * @var string
     */
    const ACTION_ID_OPTIONS = "idOptions";

    /**
     * Does this action have a next page?
     *
     * @return bool $ret true/false
     */
    public function hasNextPage();

    /**
     * Does this action have a prev page?
     *
     * @return bool $ret true/false
     */
    public function hasPrevPage();
    
    /**
     * Does this action have a first page?
     * 
     * @return bool $ret true/false
     */
    public function hasFirstPage();

    /**
     * Does this action have a last page?
     *
     * @return bool $ret true/false
     */
    public function hasLastPage();

    /**
     * Get the rel=self link for this action
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absoulte Absolute path
     *
     * @return string $url Url
     */
    public function getRefLinkUrl($router, $absoulte = false);

    /**
     * Get the "rel=next" link url
     *
     * @param RouterInterface $router   Router
     * @param bool            $absoulte Absolute path
     *
     * @return string $ret Link url
     */
    public function getNextPageUrl($router, $absoulte = false);

    /**
     * Get the "rel=prev" link url
     *
     * @param RouterInterface $router   Router
     * @param bool            $absoulte Absolute path
     *
     * @return string $ret Link url
     */
    public function getPrevPageUrl($router, $absoulte = false);

    /**
     * Get the "rel=first" link url
     *
     * @param RouterInterface $router   Router
     * @param bool            $absoulte Absolute path
     *
     * @return string $ret Link url
     */
    public function getFirstPageUrl($router, $absoulte = false);
    
    /**
     * Get the "rel=last" link url
     *
     * @param RouterInterface $router   Router
     * @param bool            $absoulte Absolute path
     *
     * @return string $ret Link url
     */
    public function getLastPageUrl($router, $absoulte = false);
}
