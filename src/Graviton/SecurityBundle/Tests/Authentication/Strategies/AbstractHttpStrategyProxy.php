<?php

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractHttpStrategyProxy
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AbstractHttpStrategyProxy extends AbstractHttpStrategy
{
    /**
     * {@inheritdoc}
     */
    public function extractFieldInfo($header, $fieldname)
    {
        return parent::extractFieldInfo($header, $fieldname);
    }

    /**
     * {@inheritdoc}
     */
    public function validateField($header, $fieldName)
    {
        parent::validateField($header, $fieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request)
    {
    }
}
