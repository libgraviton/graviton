<?php
/**
 * RqlFieldsCompilerPassTest class file
 */

namespace Graviton\Tests\Rest\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\Annotation\ClassScanner;
use Graviton\DocumentBundle\DependencyInjection\Compiler\RqlFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class RqlFieldsCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $documentMap = new DocumentMap(
            ClassScanner::getDocumentAnnotationDriver([__DIR__.'/Resources/Document/Extref']),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/extref')
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
                $this->equalTo('graviton.document.rql.fields'),
                [
                    'Graviton\Tests\Rest\DependencyInjection\CompilerPass\Resources\Document\Extref\A' => [
                        'id'                            => 'id',
                        'key'                           => 'key',
                        'ref'                           => '$exposedRefA',

                        'achild'                        => 'achild',
                        'achild.id'                     => 'achild.id',
                        'achild.key'                    => 'achild.key',
                        'achild.ref'                    => 'achild.$exposedRefB',
                        'achild.bchild'                 => 'achild.bchild',
                        'achild.bchild.id'              => 'achild.bchild.id',
                        'achild.bchild.key'             => 'achild.bchild.key',
                        'achild.bchild.ref'             => 'achild.bchild.$exposedRefC',
                        'achild.bchildren'              => 'achild.bchildren',
                        'achild.bchildren.0'            => 'achild.bchildren.0',
                        'achild.bchildren.0.id'         => 'achild.bchildren.0.id',
                        'achild.bchildren.0.key'        => 'achild.bchildren.0.key',
                        'achild.bchildren.0.ref'        => 'achild.bchildren.0.$exposedRefC',

                        'achildren'                     => 'achildren',
                        'achildren.0'                   => 'achildren.0',
                        'achildren.0.id'                => 'achildren.0.id',
                        'achildren.0.key'               => 'achildren.0.key',
                        'achildren.0.ref'               => 'achildren.0.$exposedRefB',
                        'achildren.0.bchild'            => 'achildren.0.bchild',
                        'achildren.0.bchild.id'         => 'achildren.0.bchild.id',
                        'achildren.0.bchild.key'        => 'achildren.0.bchild.key',
                        'achildren.0.bchild.ref'        => 'achildren.0.bchild.$exposedRefC',
                        'achildren.0.bchildren'         => 'achildren.0.bchildren',
                        'achildren.0.bchildren.0'       => 'achildren.0.bchildren.0',
                        'achildren.0.bchildren.0.id'    => 'achildren.0.bchildren.0.id',
                        'achildren.0.bchildren.0.key'   => 'achildren.0.bchildren.0.key',
                        'achildren.0.bchildren.0.ref'   => 'achildren.0.bchildren.0.$exposedRefC',
                    ],
                    'Graviton\Tests\Rest\DependencyInjection\CompilerPass\Resources\Document\Extref\B' => [
                        'id' => 'id',
                        'key' => 'key',
                        'ref' => '$exposedRefB',
                        'bchild' => 'bchild',
                        'bchild.id' => 'bchild.id',
                        'bchild.key' => 'bchild.key',
                        'bchild.ref' => 'bchild.$exposedRefC',
                        'bchildren' => 'bchildren',
                        'bchildren.0' => 'bchildren.0',
                        'bchildren.0.id' => 'bchildren.0.id',
                        'bchildren.0.key' => 'bchildren.0.key',
                        'bchildren.0.ref' => 'bchildren.0.$exposedRefC'
                    ],
                    'Graviton\Tests\Rest\DependencyInjection\CompilerPass\Resources\Document\Extref\C' => [
                        'id' => 'id',
                        'key' => 'key',
                        'ref' => '$exposedRefC',
                    ]
                ]
            );

        $compilerPass = new RqlFieldsCompilerPass();
        $compilerPass->process($containerDouble);
    }
}
