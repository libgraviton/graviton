<?php
/**
 * test for constraint builder calling
 */

namespace Graviton\SchemaBundle\Tests\ConstraintBuilder;

use Graviton\SchemaBundle\Constraint\ConstraintBuilder;
use Graviton\SchemaBundle\Document\Schema;
use Graviton\SchemaBundle\Tests\ConstraintBuilder\Builder\DummyBuilderA;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ConstraintBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * test the builder handling
     *
     * @return void
     */
    public function testBuilderHandling()
    {
        $sut = new ConstraintBuilder();
        $dummyBuilder = new DummyBuilderA();
        $sut->addConstraintBuilder($dummyBuilder);

        $constraints = [];
        $constraints[0] = new \stdClass();
        $constraints[0]->name = 'DummyA';
        $constraints[0]->options = [];
        $constraints[0]->options[0] = new \stdClass();
        $constraints[0]->options[0]->name = 'someOption';
        $constraints[0]->options[0]->value = 'someValue';

        $property = new Schema();
        $modelMock = $this->getMockBuilder('Graviton\RestBundle\Model\DocumentModel')
            ->disableOriginalConstructor()
            ->getMock();
        $modelMock
            ->expects($this->once())
            ->method('getConstraints')
            ->with($this->equalTo('hans'))
            ->will($this->returnValue($constraints));

        $changedProperty = $sut->addConstraints('hans', $property, $modelMock);

        $this->assertEquals('THIS WAS SET BY DUMMY-A', $changedProperty->getTitle());
        $this->assertEquals($constraints[0]->options, $dummyBuilder->options);
    }
}
