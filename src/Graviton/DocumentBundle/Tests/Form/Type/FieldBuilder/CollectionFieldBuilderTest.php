<?php
/**
 * CollectionFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\FieldBuilder\CollectionFieldBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CollectionFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test CollectionFieldBuilder::supportsField()
     *
     * @param bool   $result  Expected result
     * @param string $type    Field type
     * @param array  $options Field options
     * @return void
     *
     * @dataProvider dataSupportsField
     */
    public function testSupportsField($result, $type, array $options)
    {
        $sut = new CollectionFieldBuilder();
        $this->assertSame($result, $sut->supportsField($type, $options));
    }

    /**
     * Data for CollectionFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataSupportsField()
    {
        return [
            [true, 'collection', ['type' => 'form', 'options' => ['data_class' => 'class']]],

            [false, 'type', []],
            [false, null, []],
            [false, uniqid(), []],

            [false, 'collection', []],
            [false, 'collection', ['type' => 'form']],
            [false, 'collection', ['type' => 'form', 'options' => []]],
            [false, 'collection', ['type' => 'type', 'options' => ['data_class' => 'class']]],
        ];
    }

    /**
     * Test CollectionFieldBuilder::buildField()
     *
     * @return void
     */
    public function testBuildField()
    {
        $name = 'name';
        $type = 'type';
        $dataClass = 'data_class';
        $options = ['options' => ['data_class' => $dataClass]];
        $data = ['data'];

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
            ->with(
                $name,
                $type,
                array_merge($options, ['type' => $child, 'allow_add' => true, 'allow_delete' => true])
            );

        $sut = new CollectionFieldBuilder();
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
