<?php
/**
 * GeneratorExtensionTest
 */

namespace Graviton\GeneratorBundle\Tests\Twig;

use Graviton\GeneratorBundle\Twig\GeneratorExtension;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GeneratorExtensionTest extends \PHPUnit\Framework\TestCase
{

    /**
     * tests generation of the doctrine indexes annotation that gets generated
     *
     * @param array  $indexes       indexes
     * @param array  $ensureIndexes ensure indexes
     * @param string $expected      expected output
     *
     * @dataProvider dataProviderDoctrineIndexesAnnotation
     *
     * @return void
     */
    public function testDoctrineIndexesAnnotation($indexes, $ensureIndexes, $expected)
    {
        $sut = new GeneratorExtension();

        $idx = $sut->getDoctrineIndexesAnnotation('test', $indexes, $ensureIndexes);

        $this->assertEquals(
            $expected,
            $idx
        );
    }

    /**
     * data provider
     *
     * @return array[] data
     */
    public function dataProviderDoctrineIndexesAnnotation()
    {
        return [
            'normal' => [
                'indexes' => [
                    'hans'
                ],
                'ensureIndexes' => [
                    'fred'
                ],
                'expected' => '@ODM\Indexes({'.
                    '@ODM\Index(keys={"hans"="asc"}, name="hans", background=true), '.
                    '@ODM\Index(keys={"fred"="asc"}, name="fred", background=true)})'
            ],
            'compound-normal' => [
                'indexes' => [
                    'hans,+fred'
                ],
                'ensureIndexes' => null,
                'expected' => '@ODM\Indexes({'.
                    '@ODM\Index(keys={"hans"="asc", "fred"="asc"}, name="hans__fred", background=true)})'
            ],
            'compound-normal-desc' => [
                'indexes' => [
                    '-hans,-fred,+id'
                ],
                'ensureIndexes' => null,
                'expected' => '@ODM\Indexes({'.
                    '@ODM\Index(keys={"hans"="desc", "fred"="desc", "id"="asc"}, name="_hans__fred__id", '.
                    'background=true)})'
            ]
        ];
    }
}
