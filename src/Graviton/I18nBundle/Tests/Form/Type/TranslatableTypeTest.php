<?php
/**
 * test translatable form type
 */

namespace Graviton\I18nBundle\Tests\Form\Type;

use Graviton\I18nBundle\Form\Type\TranslatableType;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TranslatableTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testGetParent()
    {
        $sut = new TranslatableType(
            $this
                ->getMockBuilder('Graviton\I18nBundle\Service\I18nUtils')
                ->disableOriginalConstructor()
                ->getMock(),
            $this
                ->getMockBuilder('Graviton\I18nBundle\Form\DataTransformer\TranslatableToDefaultStringTransformer')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\FormType', $sut->getParent());
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $sut = new TranslatableType(
            $this
                ->getMockBuilder('Graviton\I18nBundle\Service\I18nUtils')
                ->disableOriginalConstructor()
                ->getMock(),
            $this
                ->getMockBuilder('Graviton\I18nBundle\Form\DataTransformer\TranslatableToDefaultStringTransformer')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->assertEquals('translatable', $sut->getName());
    }

    /**
     * @return void
     */
    public function testBuildForm()
    {
        $utilsDouble = $this
            ->getMockBuilder('Graviton\I18nBundle\Service\I18nUtils')
            ->disableOriginalConstructor()
            ->getMock();

        $utilsDouble
            ->expects($this->once())
            ->method('getLanguages')
            ->willReturn(['en', 'de']);
        $utilsDouble
            ->expects($this->once())
            ->method('getDefaultLanguage')
            ->willReturn('en');

        $builderDouble = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $builderDouble
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [$this->equalTo('en'), $this->equalTo('text'), $this->equalTo(['required' => true])],
                [$this->equalTo('de'), $this->equalTo('text'), $this->equalTo([])]
            );

        $sut = new TranslatableType(
            $utilsDouble,
            $this
                ->getMockBuilder('Graviton\I18nBundle\Form\DataTransformer\TranslatableToDefaultStringTransformer')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $sut->buildForm($builderDouble, []);
    }
}
