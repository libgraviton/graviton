<?php
/**
 * test generic form builder class
 */

namespace Graviton\DocumentBundle\Tests\Form\Type;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Symfony\Component\Form\FormEvents;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test DocumentType::getName()
     *
     * @return void
     * @expectedException \RuntimeException
     */
    public function testInitialize()
    {
        $class = __CLASS__;

        $sut = new DocumentType([]);
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

        $sut = new DocumentType([$class => []]);
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

        $sut = new DocumentType([$class => []]);
        $sut->initialize($class);

        $resolverDouble = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolverDouble
            ->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => $class]);
        $sut->configureOptions($resolverDouble);
    }

    /**
     * Test DocumentType::buildForm()
     *
     * @return void
     * @todo Refactor DocumentType and add unit tests for Form::submit()
     */
    public function testBuildForm()
    {
        $class = __CLASS__;

        $builderDouble = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $builderDouble
            ->expects($this->once())
            ->method('addEventListener')
            ->with(
                $this->equalTo(FormEvents::PRE_SUBMIT),
                $this->isInstanceOf('Closure')
            );

        $sut = new DocumentType([$class => []]);
        $sut->initialize($class);

        $sut->buildForm($builderDouble, []);
    }
}
