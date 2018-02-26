<?php
/**
 * PaginatorAwareInterface
 */

namespace Graviton\RestBundle\Model;

use Knp\Component\Pager\Paginator;

/**
 * PaginatorAwareInterface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
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
