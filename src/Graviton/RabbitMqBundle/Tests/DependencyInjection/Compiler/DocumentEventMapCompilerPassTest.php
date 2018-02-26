<?php
/**
 * DocumentEventMapCompilerPassTest class file
 */

namespace Graviton\RabbitMqBundle\Tests\DependencyInjection\Compiler;

use Graviton\RabbitMqBundle\DependencyInjection\Compiler\DocumentEventMapCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentEventMapCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test DocumentEventMapCompilerPass::process()
     *
     * @return void
     */
    public function testProcess()
    {
        $extRefMap = [
            'Document' => 'graviton.core.rest.app',
            'Hans' => 'gravitondyn.hans.rest.whatever'
        ];

        $newMap = [
            'Document' => [
                'baseRoute' => 'graviton.core.rest.app',
                'events' => [
                    'put' => 'document.core.app.update',
                    'patch' => 'document.core.app.update',
                    'post' => 'document.core.app.create',
                    'delete' => 'document.core.app.delete',
                    'postNoSlash' => 'document.core.app.create'
                ]
            ],
            'Hans' => [
                'baseRoute' => 'gravitondyn.hans.rest.whatever',
                'events' => [
                    'put' => 'document.hans.whatever.update',
                    'patch' => 'document.hans.whatever.update',
                    'post' => 'document.hans.whatever.create',
                    'delete' => 'document.hans.whatever.delete',
                    'postNoSlash' => 'document.hans.whatever.create'
                ]
            ]
        ];

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
                          ->disableOriginalConstructor()
                          ->getMock();
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with('graviton.document.type.extref.mapping')
            ->willReturn($extRefMap);
        $container
            ->expects($this->once())
            ->method('setParameter')
            ->with('graviton.document.eventmap', $newMap);

        $sut = new DocumentEventMapCompilerPass();
        $sut->process($container);
    }
}
