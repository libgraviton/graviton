<?php
/**
 * ChainFieldBuilderTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\FieldBuilder\ChainFieldBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ChainFieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ChainFieldBuilder::supportsField()
     *
     * @return void
     */
    public function testSupportsField()
    {
        $type = 'type';
        $options = ['option'];

        $sut = new ChainFieldBuilder();
        $this->assertFalse($sut->supportsField('type'));

        $builderOne = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builderOne->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(false);

        $builderTwo = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builderTwo->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(true);

        $builderThree = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builderThree->expects($this->never())
            ->method('supportsField');

        $sut = new ChainFieldBuilder();
        $sut->addFormFieldBuilder($builderOne);
        $sut->addFormFieldBuilder($builderTwo);
        $sut->addFormFieldBuilder($builderThree);
        $this->assertTrue($sut->supportsField($type, $options));
    }

    /**
     * Test ChainFieldBuilder::buildField()
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

        $builderOne = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builderOne->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(false);
        $builderOne->expects($this->never())
            ->method('buildField');

        $builderTwo = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builderTwo->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(true);
        $builderTwo->expects($this->once())
            ->method('buildField')
            ->with($document, $form, $name, $type, $options, $data);

        $builderThree = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builderThree->expects($this->never())
            ->method('supportsField');
        $builderThree->expects($this->never())
            ->method('buildField');

        $sut = new ChainFieldBuilder();
        $sut->addFormFieldBuilder($builderOne);
        $sut->addFormFieldBuilder($builderTwo);
        $sut->addFormFieldBuilder($builderThree);
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
