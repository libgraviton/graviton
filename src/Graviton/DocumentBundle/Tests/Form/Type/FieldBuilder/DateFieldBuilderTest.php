<?php
/**
 * DateFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\FieldBuilder\DateFieldBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DateFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test DateFieldBuilder::supportsField()
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
        $sut = new DateFieldBuilder();
        $this->assertSame($result, $sut->supportsField($type, $options));
    }

    /**
     * Data for DateFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataSupportsField()
    {
        return [
            [true, 'date', []],
            [true, 'datetime', []],

            [false, 'type', []],
            [false, null, []],
            [false, uniqid(), []],
        ];
    }

    /**
     * Test DateFieldBuilder::buildField()
     *
     * @return void
     */
    public function testBuildField()
    {
        $name = 'name';
        $type = 'type';
        $options = ['required' => true];
        $data = ['data'];

        $document = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\DocumentType')
            ->disableOriginalConstructor()
            ->getMock();

        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('add')
            ->with($name, $type, array_merge($options, ['widget' => 'single_text', 'input' => 'string']));

        $sut = new DateFieldBuilder();
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
