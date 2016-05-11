<?php
/**
 * test generic form builder class
 */

namespace Graviton\DocumentBundle\Tests\Form\Type;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldBuilderDouble;

    /**
     * Initialize test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fieldBuilderDouble = $this
            ->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->getMock();

        parent::setUp();
    }

    /**
     * Test DocumentType::initialize()
     *
     * @return void
     */
    public function testInitialize()
    {
        $class = __CLASS__;

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => []]);
        $sut->initialize($class);
    }


    /**
     * Test DocumentType::initialize() with error
     *
     * @return void
     * @expectedException \RuntimeException
     */
    public function testInitializeWithError()
    {
        $class = __CLASS__;

        $sut = new DocumentType($this->fieldBuilderDouble, []);
        $sut->initialize($class);
    }

    /**
     * Test DocumentType::getName()
     *
     * @return void
     */
    public function testGetName()
    {
        $class = __CLASS__;

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => []]);
        $sut->initialize($class);

        $this->assertEquals(strtolower(strtr($class, '\\', '_')), $sut->getName());
    }

    /**
     * Test DocumentType::configureOptions()
     *
     * @return void
     */
    public function testConfigureOptions()
    {
        $class = __CLASS__;

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => []]);
        $sut->initialize($class);

        $resolverDouble = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolverDouble
            ->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => $class,
                    'extra_fields_message' => 'This form should not contain extra fields like "{{ extra_fields }}".'
                ]
            );
        $sut->configureOptions($resolverDouble);
    }

    /**
     * Test DocumentType::buildForm()
     *
     * @return void
     */
    public function testBuildForm()
    {
        $class = __CLASS__;

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => []]);
        $sut->initialize($class);

        $builderDouble = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $builderDouble
            ->expects($this->once())
            ->method('addEventListener')
            ->with(
                FormEvents::PRE_SUBMIT,
                [$sut, 'handlePreSubmitEvent']
            );
        $sut->buildForm($builderDouble, []);
    }

    /**
     * Test DocumentType::handlePreSubmitEvent() with empty form fields
     *
     * @return void
     */
    public function testHandlePreSubmitEventWithEmptyFields()
    {
        $class = __CLASS__;
        $fields = [];

        $eventDouble = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $eventDouble
            ->expects($this->never())
            ->method('getForm');
        $eventDouble
            ->expects($this->never())
            ->method('getData');

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => $fields]);
        $sut->initialize($class);

        $this->fieldBuilderDouble
            ->expects($this->never())
            ->method('supportsField');
        $this->fieldBuilderDouble
            ->expects($this->never())
            ->method('buildField');

        $sut->handlePreSubmitEvent($eventDouble);
    }

    /**
     * Test DocumentType::handlePreSubmitEvent() with optional form and empty submmited data
     *
     * @return void
     */
    public function testHandlePreSubmitEventWithOptionalFormAndEmptySubmittedData()
    {
        $class = __CLASS__;
        $fields = [['name', 'type', ['options']]];
        $submittedData = null;

        $formDouble = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formDouble
            ->expects($this->once())
            ->method('isRequired')
            ->willReturn(false);

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => $fields]);
        $sut->initialize($class);

        $this->fieldBuilderDouble
            ->expects($this->never())
            ->method('supportsField');
        $this->fieldBuilderDouble
            ->expects($this->never())
            ->method('buildField');

        $sut->handlePreSubmitEvent(new FormEvent($formDouble, $submittedData));
    }

    /**
     * Test DocumentType::handlePreSubmitEvent() with unsupported field
     *
     * @return void
     * @expectedException \LogicException
     */
    public function testHandlePreSubmitEventWithUnsupportedField()
    {
        $class = __CLASS__;
        $fields = [['name', 'type', ['options']]];
        $submittedData = [];

        $formDouble = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formDouble
            ->expects($this->once())
            ->method('isRequired')
            ->willReturn(true);

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => $fields]);
        $sut->initialize($class);

        $this->fieldBuilderDouble
            ->expects($this->once())
            ->method('supportsField')
            ->with('type', ['options'])
            ->willReturn(false);
        $this->fieldBuilderDouble
            ->expects($this->never())
            ->method('buildField');


        $sut->handlePreSubmitEvent(new FormEvent($formDouble, $submittedData));
    }

    /**
     * Test DocumentType::handlePreSubmitEvent()
     *
     * @return void
     */
    public function testHandlePreSubmitEvent()
    {
        $class = __CLASS__;
        $fields = [
            ['name1', 'type1', ['options1']],
            ['name2', 'type2', ['options2']],
        ];
        $submittedData = [
            'name1' => 'data1',
            'name2' => 'data2',
        ];

        $formDouble = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formDouble
            ->expects($this->once())
            ->method('isRequired')
            ->willReturn(true);

        $sut = new DocumentType($this->fieldBuilderDouble, [$class => $fields]);
        $sut->initialize($class);

        $this->fieldBuilderDouble
            ->expects($this->exactly(2))
            ->method('supportsField')
            ->withConsecutive(
                ['type1', ['options1']],
                ['type2', ['options2']]
            )
            ->willReturn(true);
        $this->fieldBuilderDouble
            ->expects($this->exactly(2))
            ->method('buildField')
            ->withConsecutive(
                [$sut, $formDouble, 'name1', 'type1', ['options1'], 'data1'],
                [$sut, $formDouble, 'name2', 'type2', ['options2'], 'data2']
            );

        $sut->handlePreSubmitEvent(new FormEvent($formDouble, $submittedData));
    }
}
