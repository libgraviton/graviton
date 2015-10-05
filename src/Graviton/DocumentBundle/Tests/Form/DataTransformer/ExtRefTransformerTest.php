<?php
/**
 * ExtRefTransformerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Form\DataTransformer;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Form\DataTransformer\ExtRefTransformer;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtReferenceConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;
    /**
     * @var ExtRefTransformer
     */
    private $transformer;

    /**
     * Initialize test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->converter = $this
            ->getMockBuilder(ExtReferenceConverterInterface::class)
            ->getMock();
        $this->transformer = new ExtRefTransformer($this->converter);

        parent::setUp();
    }

    /**
     * Test ExtRefTransformer::transform() with null as extref
     *
     * @return string
     */
    public function testTransformNull()
    {
        $this->converter->expects($this->never())
            ->method('getUrl');

        $this->assertEquals('', $this->transformer->transform(null));
    }

    /**
     * Test ExtRefTransformer::transform() with invalid extref
     *
     * @return string
     */
    public function testTransformInvalid()
    {
        $extref = ExtReference::create('ref', 'id');

        $this->converter->expects($this->once())
            ->method('getUrl')
            ->with($extref)
            ->willThrowException(new \InvalidArgumentException());

        $this->setExpectedException(TransformationFailedException::class);
        $this->transformer->transform($extref);
    }


    /**
     * Test ExtRefTransformer::transform() with valid extref
     *
     * @return string
     */
    public function testTransformValid()
    {
        $extref = ExtReference::create('ref', 'id');
        $url = 'extref-url';

        $this->converter->expects($this->once())
            ->method('getUrl')
            ->with($extref)
            ->willReturn($url);

        $this->assertEquals($url, $this->transformer->transform($extref));
    }

    /**
     * Test ExtRefTransformer::reverseTransform() with empty url
     *
     * @return string
     */
    public function testReverseTransformNull()
    {
        $this->converter->expects($this->never())
            ->method('getExtReference');

        $this->assertEquals('', $this->transformer->reverseTransform(''));
        $this->assertEquals('', $this->transformer->reverseTransform(null));
    }

    /**
     * Test ExtRefTransformer::reverseTransform() with invalid extref
     *
     * @return string
     */
    public function testReverseTransformInvalid()
    {
        $url = 'extref-url';

        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($url)
            ->willThrowException(new \InvalidArgumentException());

        $this->setExpectedException(TransformationFailedException::class);
        $this->transformer->reverseTransform($url);
    }


    /**
     * Test ExtRefTransformer::reverseTransform() with valid url
     *
     * @return string
     */
    public function testReverseTransformValid()
    {
        $extref = ExtReference::create('ref', 'id');
        $url = 'extref-url';

        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($url)
            ->willReturn($extref);

        $this->assertEquals($extref, $this->transformer->reverseTransform($url));
    }
}
