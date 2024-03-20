<?php
/**
 * GeneratorExtensionTest
 */

namespace Graviton\Tests\Generator\Twig;

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
     * @return array data
     */
    public static function dataProviderDoctrineIndexesAnnotation(): array
    {
        return [
            'normal' => [
                'indexes' => [
                    'hans'
                ],
                'ensureIndexes' => [
                    'fred'
                ],
                'expected' => '#[ODM\Index(keys: [\'hans\' => \'asc\'], name: "hans_0", background: true)]'.PHP_EOL.
                    '#[ODM\Index(keys: [\'fred\' => \'asc\'], name: "fred_0", background: true)]'
            ],
            'compound-normal' => [
                'indexes' => [
                    'hans,+fred'
                ],
                'ensureIndexes' => null,
                'expected' =>
                    '#[ODM\Index(keys: [\'hans\' => \'asc\', \'fred\' => \'asc\'], '.
                    'name: "hans_0_fred_0", background: true)]'
            ],
            'compound-normal-desc' => [
                'indexes' => [
                    '-hans,-fred,+id'
                ],
                'ensureIndexes' => null,
                'expected' =>
                    '#[ODM\Index(keys: [\'hans\' => \'desc\', \'fred\' => \'desc\', \'id\' => \'asc\'], '.
                    'name: "hans_1_fred_1_id_0", background: true)]'
            ],
            'index-options' => [
                'indexes' => [
                    'dude["expireAfterSeconds"=30;"sparse"=true]'
                ],
                'ensureIndexes' => null,
                'expected' =>
                    '#[ODM\Index(keys: [\'dude\' => \'asc\'], name: "dude_0_ttl", background: true, '.
                    'options: ["expireAfterSeconds" => 30, "sparse" => true])]'
            ],
            'index-multi-options' => [
                'indexes' => [
                    'fred,-dude["expireAfterSeconds"=30;"sparse"=true]'
                ],
                'ensureIndexes' => null,
                'expected' =>
                    '#[ODM\Index(keys: [\'fred\' => \'asc\', \'dude\' => \'desc\'], name: "fred_0_dude_1_ttl", '.
                    'background: true, options: ["expireAfterSeconds" => 30, "sparse" => true])]'
            ]
        ];
    }
}
