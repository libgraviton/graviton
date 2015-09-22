<?php
/**
 * DefaultFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\FieldBuilder\DefaultFieldBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefaultFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test DefaultFieldBuilder::supportsField()
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
        $sut = new DefaultFieldBuilder();
        $this->assertSame($result, $sut->supportsField($type, $options));
    }

    /**
     * Data for DefaultFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataSupportsField()
    {
        return [
            [true, 'type', []],
            [true, null, []],
            [true, uniqid(), []],
        ];
    }
    /**
     * Test DefaultFieldBuilder::buildField()
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
            ->with(
                $name,
                $type,
                $options
            );

        $sut = new DefaultFieldBuilder();
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
