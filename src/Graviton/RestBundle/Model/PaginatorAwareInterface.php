<?php

namespace Graviton\RestBundle\Model;

use Knp\Component\Pager\Paginator;

/**
 * PaginatorAwareInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
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
