<?php
/**
 * TranslatableFieldsCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Symfony\Component\Finder\Finder;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class TranslatableFieldsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $containerDouble = $this
            ->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.type.translatable.fields'),
                [
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\A' => [
                        'title',
                        'achild.title',
                        'achild.bchild.title',
                        'achild.bchildren.0.title',
                        'achildren.0.title',
                        'achildren.0.bchild.title',
                        'achildren.0.bchildren.0.title'
                    ],
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\B' => [
                        'title',
                        'bchild.title',
                        'bchildren.0.title'
                    ],
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\C' => [
                        'title'
                    ]
                ]
            );

        $compilerPass = $this
            ->getMockBuilder('Graviton\\DocumentBundle\\DependencyInjection\\Compiler\\TranslatableFieldsCompilerPass')
            ->setMethods(['getDoctrineMappingFinder'])
            ->getMock();
        $compilerPass
            ->expects($this->any())
            ->method('getDoctrineMappingFinder')
            ->willReturn(
                (new Finder())
                    ->in(__DIR__.'/Resources/doctrine')
                    ->name('*.mongodb.xml')
            );

        $compilerPass->processServices($containerDouble, ['graviton.document.controller.A']);
    }
}
