<?php

namespace Graviton\RestBundle\Model;

use Knp\Component\Pager\Paginator;

/**
 * PaginatorAwareInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface PaginatorAwareInterface
{
    /**
     * set paginator
     *
     * @param Paginator $paginator paginator used in collection
     *
     * @return void
     */
    public function setPaginator(Paginator $paginator);

    /**
     * Determines, if there is already a paginator defined.
     *
     * @return boolean
     */
    public function hasPaginator();
}
