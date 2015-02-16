<?php
/**
 * proxy class for testing abstract strategy
 */

namespace Graviton\SecurityBundle\Tests\Authentication\Strategies;

use Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractHttpStrategyProxy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AbstractHttpStrategyProxy extends AbstractHttpStrategy
{
    /**
     * {@inheritdoc}
     *
     * @todo check why fieldname is not camel case here
     *
     * @param string $header    header
     * @param string $fieldname field name
     *
     * @return string
     */
    public function extractFieldInfo($header, $fieldname)
    {
        return parent::extractFieldInfo($header, $fieldname);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $header    header
     * @param string $fieldName field name
     *
     * @return void
     */
    public function validateField($header, $fieldName)
    {
        parent::validateField($header, $fieldName);
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $request request
     *
     * @return void
     */
    public function apply(Request $request)
    {
    }
}
