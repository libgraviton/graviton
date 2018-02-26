<?php
/**
 * TranslatableFieldsCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\TranslatableFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class TranslatableFieldsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/Resources/doctrine/translatable')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/translatable')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/validation/translatable')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/schema')
                ->name('*.json')
        );

        $containerDouble = $this
            ->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('graviton.document.map'))
            ->willReturn($documentMap);

        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                'graviton.document.type.translatable.fields',
                [
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\A' => [
                        'exposedTitleA',
                        'achild.exposedTitleB',
                        'achild.bchild.exposedTitleC',
                        'achild.bchildren.0.exposedTitleC',
                        'achildren.0.exposedTitleB',
                        'achildren.0.bchild.exposedTitleC',
                        'achildren.0.bchildren.0.exposedTitleC'
                    ],
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\B' => [
                        'exposedTitleB',
                        'bchild.exposedTitleC',
                        'bchildren.0.exposedTitleC'
                    ],
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\C' => [
                        'exposedTitleC'
                    ],
                ]
            );

        $compilerPass = new TranslatableFieldsCompilerPass();
        $compilerPass->process($containerDouble);
    }
}
