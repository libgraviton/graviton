<?php
/**
 * DocumentFieldNamesCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFieldNamesCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentFieldNamesCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $baseNamespace = 'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document';

        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/Resources/doctrine/form')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/form')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/validation/form')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/schema')
                ->name('*.json')
        );

        $containerDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
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
                'graviton.document.field.names',
                [
                    $baseNamespace.'\A' => [
                        'id'        => 'id',
                        'integer'   => '$integerA',
                        'title'     => '$titleA',
                        'extref'    => '$extrefA',
                        'boolean'   => '$booleanA',
                        'datetime'  => '$datetimeA',
                        'float'     => '$floatA',
                        'unstruct'  => '$unstructA',
                        'achild'    => '$achild',
                        'achildren' => '$achildren',
                    ],
                    $baseNamespace.'\B' => [
                        'id'        => 'id',
                        'field'     => '$fieldB',
                        'bchild'    => '$bchild',
                        'bchildren' => '$bchildren',
                    ],
                    $baseNamespace.'\C' => [
                        'id'        => 'id',
                        'field'     => '$fieldC',
                    ],
                ]
            );

        $compilerPass = new DocumentFieldNamesCompilerPass();
        $compilerPass->process($containerDouble);
    }
}
