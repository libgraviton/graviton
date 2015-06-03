<?php
/**
 * test generic form builder class
 */

namespace Graviton\DocumentBundle\Tests\Form\Type;

use Graviton\DocumentBundle\Form\Type\DocumentType;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testData
     *
     * @param string $class class name
     * @param string $name  expected name
     *
     * @return void
     */
    public function testGetName($class, $name)
    {
        $sut = new DocumentType($class);

        $this->assertEquals($name, $sut->getName());
    }

    /**
     * @dataProvider testData
     *
     * @param string $class class name
     *
     * @return void
     */
    public function testSetDefaultOptions($class)
    {
        $resolverDouble = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $resolverDouble->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => $class]);

        $sut = new DocumentType($class);

        $sut->setDefaultOptions($resolverDouble);
    }

    /**
     * @return array
     */
    public function testData()
    {
        return [
            ['Graviton\CoreBundle\Document\App', 'graviton_corebundle_document_app'],
        ];
    }
}
