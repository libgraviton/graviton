<?php
/**
 * SolrDefinitionCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\Annotation\ClassScanner;
use Graviton\DocumentBundle\DependencyInjection\Compiler\SolrDefinitionCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrDefinitionCompilerPassTest extends TestCase
{
    /**
     * test the processing
     *
     * @return void
     */
    public function testProcess()
    {
        $customSorter = 'if(def(field1,false),1, if( def(field2,false),2,3 )  ) asc, score desc';

        $_ENV['SOLR_A_SORT'] = $customSorter;

        $documentMap = new DocumentMap(
            ClassScanner::getDocumentAnnotationDriver([__DIR__.'/Resources/Document/Extref']),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/extref')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/schema')
                ->name('*.json')
        );

        $containerDouble = $this->createMock(ContainerBuilder::class);

        $containerDouble
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('graviton.document.map'))
            ->willReturn($documentMap);

        $double = new \ArrayObject();

        $containerDouble
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(
                function ($paramA, $paramB) use ($double) {
                    $double[$paramA] = $paramB;
                }
            );

        $sut = new SolrDefinitionCompilerPass();
        $sut->process($containerDouble);

        $expectedResult = [
            'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Extref\A' =>
                'fieldA^1 fieldB^15 fieldD^0.3'
        ];

        $expectedResultSort = [
            'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Extref\A' =>
                $customSorter
        ];

        $this->assertEquals(
            $expectedResult,
            $double['graviton.document.solr.map']
        );

        $this->assertEquals(
            $expectedResultSort,
            $double['graviton.document.solr.map_sort']
        );

        unset($_ENV['SOLR_A_SORT']);
    }
}
