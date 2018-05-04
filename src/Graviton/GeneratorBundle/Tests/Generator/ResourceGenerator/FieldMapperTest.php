<?php
/**
 * validate field mapper
 */

namespace Graviton\GeneratorBundle\Tests\Generator\ResourceGenerator;

use Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldMapper;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testWillCallMapperr()
    {
        $sut = new FieldMapper;

        $context = new \StdClass;

        $mapperDouble = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldMapperInterface');
        $mapperDouble->expects($this->exactly(2))
            ->method('map')
            ->with([], $context)
            ->willReturn([]);

        $sut->addMapper($mapperDouble);
        $sut->addMapper($mapperDouble);

        $this->assertEquals([], $sut->map([], $context));
    }
}
