<?php
/**
 * test generic form builder class
 */

namespace Graviton\DocumentBundle\Tests\Form\Type;

use Graviton\DocumentBundle\Form\Type\DocumentType;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $classMap;

    /**
     * @var array
     */
    private $fieldMap;

    /**
     * prepare env for sut
     *
     * @return void
     */
    public function setUp()
    {
        $this->classMap = [
            'graviton.core.controller.app' => 'Graviton\CoreBundle\Document\App',
            'Graviton\CoreBundle\Document\App' => 'Graviton\CoreBundle\Document\App',
        ];
        $this->fieldMap = [
            'Graviton\CoreBundle\Document\App' => [
                ['title', 'translatable', []],
                ['showInMenu', 'checkbox', []],
            ],
        ];
    }

    /**
     * @dataProvider testData
     *
     * @param string $class class name
     * @param string $name  expected name
     *
     * @return void
     */
    public function testGetName($class, $name)
    {
        $sut = new DocumentType($this->classMap, $this->fieldMap);
        $sut->initialize($class);

        $this->assertEquals($name, $sut->getName());
    }

    /**
     * @dataProvider testData
     *
     * @param string $class class name
     *
     * @return void
     */
    public function testSetDefaultOptions($class)
    {
        $resolverDouble = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $resolverDouble->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => $this->classMap[$class]]);

        $sut = new DocumentType($this->classMap, $this->fieldMap);
        $sut->initialize($class);

        $sut->setDefaultOptions($resolverDouble);
    }

    /**
     * @dataProvider testData
     *
     * @param string $class  class name
     * @param string $name   form name
     * @param array  $fields fields for builder
     *
     * @return void
     */
    public function testBuildForm($class, $name, $fields)
    {
        $builderDouble = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $i = 0;
        foreach ($fields as $field) {
            $builderDouble
                ->expects($this->at($i++))
                ->method('add')
                ->with($field['name'], $field['type'], $field['options']);
        }
        $sut = new DocumentType($this->classMap, $this->fieldMap);
        $sut->initialize($class);

        $sut->buildForm($builderDouble, []);
        $this->assertEquals($name, $sut->getName());
    }

    /**
     * @return array
     */
    public function testData()
    {
        return [
            'build from classname' => [
                'Graviton\CoreBundle\Document\App',
                'graviton_corebundle_document_app',
                [
                    [
                        'name' => 'title',
                        'type' => 'translatable', # alias to i18n form service
                        'options' => [],
                    ],
                    [
                        'name' => 'showInMenu',
                        'type' => 'checkbox',
                        'options' => [],
                    ],
                ],
            ],
            'build from service id' => [
                'graviton.core.controller.app',
                'graviton_corebundle_document_app',
                [
                    [
                        'name' => 'title',
                        'type' => 'translatable', # alias to i18n form service
                        'options' => [],
                    ],
                    [
                        'name' => 'showInMenu',
                        'type' => 'checkbox',
                        'options' => [],
                    ],
                ],
            ],
        ];
    }
}
