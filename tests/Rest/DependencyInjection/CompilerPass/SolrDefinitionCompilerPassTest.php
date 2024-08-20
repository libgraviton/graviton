<?php
/**
 * SolrDefinitionCompilerPassTest class file
 */

namespace Graviton\Tests\Rest\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\SolrDefinitionCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $_ENV['SOLR_A_BOOST'] = 'feld^2';
        $_ENV['SOLR_A_LITERAL_BRIDGE'] = '99';
        $_ENV['SOLR_B_BOOST'] = 'feld^3';
        $_ENV['SOLR_B_WILDCARD_BRIDGE'] = '999';
        $_ENV['SOLR_B_ANDIFY_TERMS'] = 'true';

        $containerDouble = $this->createMock(ContainerBuilder::class);

        $double = new \ArrayObject();

        $containerDouble
            ->expects($this->exactly(1))
            ->method('setParameter')
            ->willReturnCallback(
                function ($paramA, $paramB) use ($double) {
                    $double[$paramA] = $paramB;
                }
            );

        $sut = new SolrDefinitionCompilerPass();
        $sut->process($containerDouble);

        $params = [
            'A' => [
                'sort' => 'if(def(field1,false),1, if( def(field2,false),2,3 )  ) asc, score desc',
                'boost' => 'feld^2',
                'LITERAL_BRIDGE' => 99
            ],
            'B' => [
                'boost' => 'feld^3',
                'WILDCARD_BRIDGE' => '999',
                'ANDIFY_TERMS' => 'true'
            ]
        ];

        $this->assertEquals(
            $params,
            $double['graviton.document.solr.extra_params']
        );

        unset($_ENV['SOLR_A_SORT']);
        unset($_ENV['SOLR_A_BOOST']);
        unset($_ENV['SOLR_A_LITERAL_BRIDGE']);
        unset($_ENV['SOLR_B_BOOST']);
        unset($_ENV['SOLR_B_WILDCARD_BRIDGE']);
        unset($_ENV['SOLR_B_ANDIFY_TERMS']);
    }
}
