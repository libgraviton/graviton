<?php
/**
 * RqlQueryDecoratorCompilerPassTest class file
 */

namespace Graviton\RestBundle\Tests\DependencyInjection\Compiler;

use Graviton\RestBundle\DependencyInjection\Compiler\RqlQueryDecoratorCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlQueryDecoratorCompilerPassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test RqlQueryDecoratorCompilerPass::process()
     *
     * @return void
     */
    public function testProcess()
    {
        $tags = [
            'tag1' => [
                ['attr1' => 'value1', 'attr2' => 'value2'],
                ['attr3' => 'value3', 'attr4' => 'value4'],
            ],
            'tag2' => [
                ['attr5' => 'value5', 'attr6' => 'value6'],
                ['attr7' => 'value7', 'attr8' => 'value8'],
            ],
        ];

        $innerDefinition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $innerDefinition
            ->expects($this->once())
            ->method('getTags')
            ->willReturn($tags);
        $innerDefinition
            ->expects($this->once())
            ->method('clearTags');

        $outerDefinition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $outerDefinition
            ->expects($this->exactly(4))
            ->method('addTag')
            ->withConsecutive(
                ['tag1', ['attr1' => 'value1', 'attr2' => 'value2']],
                ['tag1', ['attr3' => 'value3', 'attr4' => 'value4']],
                ['tag2', ['attr5' => 'value5', 'attr6' => 'value6']],
                ['tag2', ['attr7' => 'value7', 'attr8' => 'value8']]
            );

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->expects($this->at(0))
            ->method('getDefinition')
            ->with('graviton.rest.listener.rqlqueryrequestlistener.inner')
            ->willReturn($innerDefinition);
        $container
            ->expects($this->at(1))
            ->method('getDefinition')
            ->with('graviton.rest.listener.rqlqueryrequestlistener')
            ->willReturn($outerDefinition);

        $sut = new RqlQueryDecoratorCompilerPass();
        $sut->process($container);
    }
}
