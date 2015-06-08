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
                ->getMockBuilder('Graviton\I18nBundle\Repository\LanguageRepository')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->assertEquals('form', $sut->getParent());
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $sut = new TranslatableType(
            $this
                ->getMockBuilder('Graviton\I18nBundle\Repository\LanguageRepository')
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
        $builderDouble = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $repoDouble = $this
            ->getMockBuilder('Graviton\I18nBundle\Repository\LanguageRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $langDouble = $this->getMock('Graviton\I18nBundle\Document\Language');

        $repoDouble
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$langDouble, $langDouble]);

        $langDouble
            ->method('getId')
            ->will($this->onConsecutiveCalls('en', 'de'));

        $builderDouble
            ->expects($this->at(0))
            ->method('add')
            ->with('en', 'text', []);
        $builderDouble
            ->expects($this->at(1))
            ->method('add')
            ->with('de', 'text', []);

        $sut = new TranslatableType($repoDouble);
        $sut->buildForm($builderDouble, []);
    }
}
