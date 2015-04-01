<?php
/**
 * verify extref custom type
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Graviton\DocumentBundle\Types\ExtReference;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @expectedException RuntimeException
     *
     * @return void
     */
    public function testExceptWithoutRouter()
    {
        Type::registerType('extref', 'Graviton\DocumentBundle\Types\ExtReference');
        $sut = Type::getType('extref');

        $sut->convertToDatabaseValue('');
    }

}
