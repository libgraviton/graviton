<?php
/**
 * ExtReferenceValidatorTest class file
 */

namespace Graviton\RestBundle\Tests\Validator\ExtReference;

use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Graviton\RestBundle\Validator\Constraints\ExtReference\ExtReference;
use Graviton\RestBundle\Validator\Constraints\ExtReference\ExtReferenceValidator;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * ExtReferenceValidator test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtReferenceConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;
    /**
     * @var ExecutionContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;


    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        $this->converter = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceConverterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl', 'getDbRef'])
            ->getMock();
        $this->converter->expects($this->never())
            ->method('getUrl');

        $this->context = $this->getMockBuilder('\Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(['addViolation'])
            ->getMock();
    }

    /**
     * Create validator
     *
     * @return ExtReferenceValidator
     */
    private function createValidator()
    {
        $validator = new ExtReferenceValidator();
        $validator->setConverter($this->converter);
        $validator->initialize($this->context);

        return $validator;
    }

    /**
     * Test validate()
     *
     * @return void
     * @expectedException \InvalidArgumentException
     */
    public function testValidateInvalidConstraint()
    {
        $url = __METHOD__;
        $constraint = new Choice();

        $this->converter->expects($this->never())
            ->method('getDbRef');

        $validator = $this->createValidator();
        $validator->validate($url, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateWithException()
    {
        $url = __METHOD__;
        $constraint = new ExtReference();

        $this->converter->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willThrowException(new \InvalidArgumentException());

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->invalidMessage, ['%url%' => $url]);

        $validator = $this->createValidator();
        $validator->validate($url, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateNotAllowed()
    {
        $url = __METHOD__;
        $extref = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = ['Product'];

        $this->converter->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willReturn($extref);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->notAllowedMessage, ['%url%' => $url]);

        $validator = $this->createValidator();
        $validator->validate($url, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateAllowedEmpty()
    {
        $url = __METHOD__;
        $extref = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = null;

        $this->converter->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willReturn($extref);

        $this->context->expects($this->never())
            ->method('addViolation');

        $validator = $this->createValidator();
        $validator->validate($url, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateAllowedAll()
    {
        $url = __METHOD__;
        $extref = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = ['*'];

        $this->converter->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willReturn($extref);

        $this->context->expects($this->never())
            ->method('addViolation');

        $validator = $this->createValidator();
        $validator->validate($url, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateAllowedNeeded()
    {
        $url = __METHOD__;
        $extref = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = [__METHOD__];

        $this->converter->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willReturn($extref);

        $this->context->expects($this->never())
            ->method('addViolation');

        $validator = $this->createValidator();
        $validator->validate($url, $constraint);
    }
}
