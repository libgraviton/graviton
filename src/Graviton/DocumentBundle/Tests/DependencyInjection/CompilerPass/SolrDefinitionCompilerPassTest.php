<?php
/**
 * SolrDefinitionCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\Annotation\ClassScanner;
use Graviton\DocumentBundle\DependencyInjection\Compiler\SolrDefinitionCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrDefinitionCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test the processing
     *
     * @return void
     */
    public function testProcess()
    {
        $expectedResult = [
            'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Extref\A' =>
                'fieldA^1 fieldB^15 fieldD^0.3'
        ];

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
                'graviton.document.solr.map',
                $expectedResult
            );

        $sut = new SolrDefinitionCompilerPass();
        $sut->process($containerDouble);
    }
}
