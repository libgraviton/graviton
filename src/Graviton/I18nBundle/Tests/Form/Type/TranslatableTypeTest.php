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
        $sut = new TranslatableType;
        $this->assertEquals('form', $sut->getParent());
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $sut = new TranslatableType;
        $this->assertEquals('translatable', $sut->getName());
    }
}
