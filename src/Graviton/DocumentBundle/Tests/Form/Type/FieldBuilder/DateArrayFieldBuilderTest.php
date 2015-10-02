<?php
/**
 * DateArrayFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\DocumentBundle\Form\Type\FieldBuilder\DateArrayFieldBuilder;
use Symfony\Component\Form\FormInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DateArrayFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test DateArrayFieldBuilder::supportsField()
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
        $sut = new DateArrayFieldBuilder();
        $this->assertSame($result, $sut->supportsField($type, $options));
    }

    /**
     * Data for DateArrayFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataSupportsField()
    {
        return [
            [false, 'type', []],
            [false, null, []],
            [false, uniqid(), []],

            [true, 'datearray', []],
            [true, 'datearray', ['aaa']],
        ];
    }

    /**
     * Test DateArrayFieldBuilder::buildField()
     *
     * @return void
     */
    public function testBuildField()
    {
        $name = 'name';
        $type = 'datearray';
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
            ->with(
                $name,
                'collection',
                array_merge(
                    $options,
                    [
                        'type' => 'datetime',
                        'allow_add' => true,
                        'allow_delete' => true,
                        'options' => ['widget' => 'single_text', 'input' => 'string'],
                    ]
                )
            );

        $sut = new DateArrayFieldBuilder();
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
