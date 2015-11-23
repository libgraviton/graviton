<?php
/**
 * BooleanTransformerTest class file
 */

namespace Form\DataTransformer;

use Graviton\DocumentBundle\Form\DataTransformer\BooleanTransformer;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class BooleanTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BooleanTransformer
     */
    private $sut;

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sut = new BooleanTransformer();

        parent::setUp();
    }

    /**
     * Data for DefaultFieldBuilder::supportsField() test
     *
     * @return array
     */
    public function dataTransform()
    {
        $propertyPath = new PropertyPath('aBoolean');

        return [
            [true, true, $propertyPath, '1'],
            [false, false, $propertyPath, null],
            [true, [true], new PropertyPath('[0]'), '1'],
            [false, [true, false], new PropertyPath('[1]'), null],
            ['1', '1', $propertyPath, '1'],
            ['blabla', 'blabla', $propertyPath, 'blabla'],
            ['true', ['true', 'false'], new PropertyPath('[0]'), 'true'],
        ];
    }

    /**
     * test the reverse transform method
     *
     * @param mixed        $result       expected result
     * @param mixed        $data         original data
     * @param PropertyPath $propertyPath property path
     * @param mixed        $value        value which will be transformed
     * @return void
     *
     * @dataProvider dataTransform
     */
    public function testReverseTransform($result, $data, $propertyPath, $value)
    {
        $this->sut->setSubmittedData($data);
        $this->sut->setPropertyPath($propertyPath);
        $this->assertSame($result, $this->sut->reverseTransform($value));
    }

    /**
     * test transform method
     *
     * @return void
     */
    public function testTransform()
    {
        $this->assertTrue($this->sut->transform(true));
        $this->assertFalse($this->sut->transform(false));
    }

    /**
     * test no property path is
     *
     * @return void
     *
     * @expectedException \RuntimeException
     */
    public function testNoPropertyPath()
    {
        $this->assertTrue($this->sut->reverseTransform('1'));
    }
}
