<?php

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface StrategyInterface
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface StrategyInterface
{
    /**
     * Applies the defined strategy on the provided request.
     *
     * @param Request $request
     *
     * @return string
     */
    public function apply(Request $request);
}
