<?php
/**
 * validate XDynamicKey
 */

namespace Graviton\GeneratorBundle\Tests\Generator\ResourceGenerator;

use Graviton\GeneratorBundle\Generator\ResourceGenerator\XDynamicKey;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class XDynamicKeyTest extends \PHPUnit\Framework\TestCase
{

    /**
     * test if it does resolve
     *
     * @return void
     */
    public function testResolveRef()
    {
        $c = $this->getMockBuilder('ClassC')
            ->setMethods(array('getId'))
            ->getMock();
        $c->method('getId')
            ->willReturn('someRandomId');

        $b = $this->getMockBuilder('ClassB')
            ->setMethods(array('getRef'))
            ->getMock();
        $b->method('getRef')
            ->willReturn($c);

        $a = $this->getMockBuilder('ClassA')
            ->setMethods(array('getB'))
            ->getMock();
        $a->method('getB')
            ->willReturn($b);

        $this->assertArrayHasKey('someRandomId', XDynamicKey::resolveRef([$a], 'b.ref'));
    }

    /**
     * test only one field in method variable
     *
     * @return void
     */
    public function testResolveRefOne()
    {
        $b = $this->getMockBuilder('ClassB')
            ->setMethods(array('getId'))
            ->getMock();
        $b->method('getId')
            ->willReturn('someRandomId');

        $a = $this->getMockBuilder('ClassA')
            ->setMethods(array('getRef'))
            ->getMock();
        $a->method('getRef')
            ->willReturn($b);

        $this->assertArrayHasKey('someRandomId', XDynamicKey::resolveRef([$a], 'ref'));
    }

    /**
     * test empty vars
     *
     * @return void
     */
    public function testResolveRefMethodEmptyVars()
    {
        $this->assertEmpty(XDynamicKey::resolveRef([], ''));
    }

    /**
     * test if it behaves correctly if the methods don't exist
     *
     * @expectedException Graviton\ExceptionBundle\Exception\XDynamicKeyException
     * @return void
     */
    public function testResolveRefNotExistingFields()
    {
        $c = $this->getMockBuilder('ClassC')
            ->setMethods(array('getId'))
            ->getMock();
        $c->method('getId')
            ->willReturn('someRandomId');

        $b = $this->getMockBuilder('ClassB')
            ->setMethods(array('getRef'))
            ->getMock();
        $b->method('getRef')
            ->willReturn($c);

        $a = $this->getMockBuilder('ClassA')
            ->setMethods(array('getB'))
            ->getMock();
        $a->method('getB')
            ->willReturn($b);

        XDynamicKey::resolveRef([$a], 'some.thing.that.doesnt.exist');
    }
}
