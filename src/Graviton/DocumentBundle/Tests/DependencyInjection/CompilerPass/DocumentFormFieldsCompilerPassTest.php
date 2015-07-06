<?php
/**
 * check if form builder field-map is being generated correctly
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormFieldsCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormFieldsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $containerDouble = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $serviceDouble = $this->getMock('Symfony\Component\DependencyInjection\Definition');

        $containerDouble
            ->method('getDefinition')
            ->willReturn($serviceDouble);

        $containerDouble
            ->expects($this->at(0))
            ->method('getParameter')
            ->with('graviton.document.form.type.document.service_map')
            ->willReturn(
                [
                    'graviton.core.controller.app' => '%graviton.core.document.app.class%',
                ]
            );

        $containerDouble
            ->expects($this->at(1))
            ->method('findTaggedServiceIds')
            ->with('graviton.rest')
            ->willReturn([]);

        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.form.type.document.field_map'),
                ['stdclass' => 'stdclass']
            );

        $sut = new DocumentFormFieldsCompilerPass;
        $sut->process($containerDouble);
    }
}
