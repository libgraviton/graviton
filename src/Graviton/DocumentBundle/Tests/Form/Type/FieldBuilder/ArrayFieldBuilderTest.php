<?php
/**
 * ArrayFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\DocumentBundle\Form\Type\FieldBuilder\ArrayFieldBuilder;
use Symfony\Component\Form\FormInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ArrayFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ArrayFieldBuilder::supportsField()
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
        $sut = new ArrayFieldBuilder();
        $this->assertSame($result, $sut->supportsField($type, $options));
    }

    /**
     * Data for ArrayFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataSupportsField()
    {
        return [
            [false, 'type', []],
            [false, null, []],
            [false, uniqid(), []],

            [false, 'collection', []],
            [false, 'collection', ['type' => 'form']],

            [true, 'collection', ['type' => 'integer']],
            [true, 'collection', ['type' => 'string']],
        ];
    }

    /**
     * Test ArrayFieldBuilder::buildField()
     *
     * @return void
     */
    public function testBuildField()
    {
        $name = 'name';
        $type = 'collection';
        $options = ['required' => true];
        $data = ['data'];

        $document = $this->getMockBuilder(DocumentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $form = $this->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('add')
            ->with($name, $type, array_merge($options, ['allow_add' => true, 'allow_delete' => true]));

        $sut = new ArrayFieldBuilder();
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
