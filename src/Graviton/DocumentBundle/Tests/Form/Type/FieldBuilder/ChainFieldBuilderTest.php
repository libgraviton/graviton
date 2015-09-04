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

        $builder1 = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builder1->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(false);

        $builder2 = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builder2->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(true);

        $builder3 = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builder3->expects($this->never())
            ->method('supportsField');

        $sut = new ChainFieldBuilder();
        $sut->addFormFieldBuilder($builder1);
        $sut->addFormFieldBuilder($builder2);
        $sut->addFormFieldBuilder($builder3);
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

        $builder1 = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builder1->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(false);
        $builder1->expects($this->never())
            ->method('buildField');

        $builder2 = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builder2->expects($this->once())
            ->method('supportsField')
            ->with($type, $options)
            ->willReturn(true);
        $builder2->expects($this->once())
            ->method('buildField')
            ->with($document, $form, $name, $type, $options, $data);

        $builder3 = $this->getMockBuilder('Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $builder3->expects($this->never())
            ->method('supportsField');
        $builder3->expects($this->never())
            ->method('buildField');

        $sut = new ChainFieldBuilder();
        $sut->addFormFieldBuilder($builder1);
        $sut->addFormFieldBuilder($builder2);
        $sut->addFormFieldBuilder($builder3);
        $sut->buildField($document, $form, $name, $type, $options, $data);
    }
}
