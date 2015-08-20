<?php
/**
 * ExtRefMappingCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\ExtRefMappingCompilerPass;

/**
 * ExtRefMappingCompilerPass test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefMappingCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $serviceId      Service ID
     * @param array  $serviceTags    Service tags
     * @param array  $expectedResult Expected result
     * @return void
     *
     * @dataProvider dataProcess
     */
    public function testProcess($serviceId, array $serviceTags, array $expectedResult)
    {
        $serviceDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceDouble
            ->expects($this->once())
            ->method('getTag')
            ->with('graviton.rest')
            ->willReturn($serviceTags);

        $containerDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('getDefinition')
            ->with($serviceId)
            ->willReturn($serviceDouble);
        $containerDouble
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('graviton.rest')
            ->willReturn([$serviceId => []]);

        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                'graviton.document.type.extref.mapping',
                $expectedResult
            );

        $sut = new ExtRefMappingCompilerPass();
        $sut->process($containerDouble);
    }

    /**
     * @return array
     */
    public function dataProcess()
    {
        return [
            [
                'graviton.core.controller.noapp',
                [['collection' => 'App']],
                ['App' => 'graviton.core.rest.noapp.get']
            ],
            [
                'graviton.core.controller.config',
                [],
                ['Config' => 'graviton.core.rest.config.get']
            ],
        ];
    }
}
