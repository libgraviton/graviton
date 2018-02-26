<?php
/**
 * validate resource-generators param builder
 */

namespace Graviton\GeneratorBundle\Tests\Generator\ResourceGenerator;

use Graviton\GeneratorBundle\Generator\ResourceGenerator\ParameterBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ParameterBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider buildBasicParamData
     *
     * @param string $document document name
     * @param string $index    param index
     *
     * @return void
     */
    public function testBuildDocumentParam($document, $index)
    {
        $sut = new ParameterBuilder();

        $this->assertEquals($sut, $sut->setParameter($index, $document));
        $parameters = $sut->getParameters();
        $this->assertEquals($document, $parameters[$index]);
    }

    /**
     * @return array
     */
    public function buildBasicParamData()
    {
        return [
            ['document', 'document'],
            ['My\Longer\Document\Name', 'document'],
            ['someBaseBundleNamespace', 'base'],
            ['BundleName', 'bundle'],
        ];
    }

    /**
     * @dataProvider buildBasenameParams
     *
     * @param string $basename    basename
     * @param string $underscored underscored form of basename
     *
     * @return void
     */
    public function testBuildBasenameParams($basename, $underscored)
    {
        $sut = new ParameterBuilder();

        $this->assertEquals($sut, $sut->setParameter('basename', $basename));

        $parameters = $sut->getParameters();
        $this->assertEquals($basename, $parameters['bundle_basename']);
        $this->assertEquals($underscored, $parameters['extension_alias']);
    }

    /**
     * @return array
     */
    public function buildBasenameParams()
    {
        return [
            ['Name', 'name'],
            ['BaseName', 'base_name'],
        ];
    }

    /**
     * @dataProvider buildJsonParamsIdFieldDefData
     *
     * @param array|null $idFieldDef field definition or null if not a json def
     * @param string     $parent     parent service name
     *
     * @return void
     */
    public function testBuildJsonParamsIdFieldDef($idFieldDef, $parent = null)
    {
        $sut = new ParameterBuilder;

        $jsonDefDouble = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $fieldDouble = null;
        if (!is_null($idFieldDef)) {
            $fieldDouble = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\JsonDefinitionField')
                ->disableOriginalConstructor()
                ->getMock();
        }

        $jsonDefDouble->expects($this->once())
            ->method('getField')
            ->willReturn($fieldDouble);

        if (!is_null($idFieldDef)) {
            $fieldDouble->expects($this->once())
                ->method('getDefAsArray')
                ->willReturn($idFieldDef);
        }

        if (!is_null($parent)) {
            $jsonDefDouble
                ->expects($this->once())
                ->method('getParentService')
                ->willReturn($parent);
        }

        $this->assertEquals($sut, $sut->setParameter('json', $jsonDefDouble));

        $parameters = $sut->getParameters();

        if (is_null($idFieldDef)) {
            $idFieldDef = [];
        }
        $expected = ['json' => $jsonDefDouble, 'parent' => $parent];
        if ($idFieldDef === []) {
            $expected['noIdField'] = true;
        } else {
            $expected['idField'] = $idFieldDef;
        }
        $this->assertEquals($expected, $parameters);
    }

    /**
     * @return array
     */
    public function buildJsonParamsIdFieldDefData()
    {
        return [
            [null],
            [['type' => 'string']],
            [['type' => 'int'], 'parent.service'],
        ];
    }
}
