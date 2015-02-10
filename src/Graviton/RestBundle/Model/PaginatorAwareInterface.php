<?php
/**
 * PaginatorAwareInterface
 *
 * PHP Version 5
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\RestBundle\Model;

use Knp\Component\Pager\Paginator;

/**
 * PaginatorAwareInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
