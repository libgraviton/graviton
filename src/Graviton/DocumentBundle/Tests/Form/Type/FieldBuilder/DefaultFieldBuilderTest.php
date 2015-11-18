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
     * @var Graviton\DocumentBundle\Form\Type\DocumentType
     */
    private $document;

    /**
     * @var Symfony\Component\Form\FormInterface
     */
    private $form;

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function setUp()
    {
        $this->document = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\DocumentType')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

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
     * Test DefaultFieldBuilder::buildField() with boolean
     *
     * @param string $name          name of the form type
     * @param string $type          form type
     * @param arry   $options       options
     * @param array  $methodOptions options for the add method
     * @param mixed  $data          submitted data
     * @return void
     *
     * @dataProvider dataForBuildField
     */
    public function testBuildFieldWithBoolean($name, $type, $options, $methodOptions, $data)
    {
        $this->form->expects($this->once())
            ->method('add')
            ->with(
                $name,
                $type,
                $methodOptions
            );

        $sut = new DefaultFieldBuilder();
        $sut->buildField($this->document, $this->form, $name, $type, $options, $data);
    }

    /**
     * Data for DefaultFieldBuilder::buildField()
     *
     * @return array
     */
    public function dataForBuildField()
    {
        $options = ['required' => true];

        return [
            ['name', 'type', $options, $options, ['data']],
            ['aBoolean', 'strictboolean', $options, array_merge($options, ['submitted_data' => true]), true],
        ];
    }
}
