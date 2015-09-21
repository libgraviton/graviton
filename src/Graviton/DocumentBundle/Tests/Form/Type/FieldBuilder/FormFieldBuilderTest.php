<?php
/**
 * FormFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\FieldBuilder\FormFieldBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FormFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test FormFieldBuilder::supportsField()
     *
     * @param bool   $result  Expected result
     * @param string $type    Field type
     * @param array  $options Field options
     * @return void
     *
     * @dataProvider dataSupportsField
     */
    public function testSupportsField($result, $type, array $options = [])
    {
        $sut = new FormFieldBuilder();
        $this->assertSame($result, $sut->supportsField($type, $options));
    }

    /**
     * Data for FormFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataSupportsField()
    {
        return [
            [true, 'form', []],
            [true, 'form', ['options']],

            [false, 'type', []],
            [false, null, []],
            [false, uniqid(), []],
        ];
    }

    /**
     * Test FormFieldBuilder::buildField()
     *
     * @param array $fieldOptions  Field options
     * @param mixed $submittedData Submitted data
     * @param array $extraOptions  Options that will be set in field builder
     * @return void
     *
     * @dataProvider dataBuildField
     */
    public function testBuildField(array $fieldOptions, $submittedData, array $extraOptions)
    {
        $name = 'name';
        $type = 'type';
        $dataClass = 'data_class';
        $options = array_merge(['data_class' => $dataClass], $fieldOptions);

        $child = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\DocumentType')
            ->disableOriginalConstructor()
            ->getMock();
        $document = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\DocumentType')
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->once())
            ->method('getChildForm')
            ->with($dataClass)
            ->willReturn($child);

        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('add')
            ->with($name, $child, array_merge($options, $extraOptions));

        $sut = new FormFieldBuilder();
        $sut->buildField($document, $form, $name, $type, $options, $submittedData);
    }

    /**
     * Data for FormFieldBuilder::buildField() test
     *
     * @return array
     */
    public function dataBuildField()
    {
        return [
            'default and empty data' => [
                [],
                null,
                ['required' => false],
            ],
            'default and non-empty data' => [
                [],
                ['data'],
                ['required' => true],
            ],

            'required form and empty data' => [
                ['required' => true],
                null,
                ['required' => true],
            ],
            'required form and non-empty data' => [
                ['required' => true],
                ['data'],
                ['required' => true],
            ],

            'optional form and empty data' => [
                ['required' => false],
                null,
                ['required' => false],
            ],
            'optional form and non-empty data' => [
                ['required' => false],
                ['data'],
                ['required' => true],
            ],
        ];
    }
}
