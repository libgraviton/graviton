<?php
/**
 * BaseTypeTestCase class file
 */

namespace Graviton\DocumentBundle\Tests\Types;

/**
 * Base Doctrine Type test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BaseDoctrineTypeTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Assert that expected result is equal to closure return value
     *
     * @param mixed  $expected      Expected value
     * @param mixed  $value         This value will be passed to closure
     * @param string $closureString Closure to eval
     * @return void
     */
    public function assertEqualsClosure($expected, $value, $closureString)
    {
        $return = null;
        eval($closureString);

        $this->assertEquals($expected, $return);
    }
}
