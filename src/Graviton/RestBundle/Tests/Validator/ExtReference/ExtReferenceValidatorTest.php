<?php
/**
 * ExtReferenceValidatorTest class file
 */

namespace Graviton\RestBundle\Tests\Validator\ExtReference;

use Graviton\DocumentBundle\Entity\ExtReference as ExtRef;
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
        $validator->initialize($this->context);

        return $validator;
    }

    /**
     * Test validate()
     *
     * @return void
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateInvalidConstraint()
    {
        $extref = ExtRef::create(__FILE__, __FUNCTION__);
        $constraint = new Choice();

        $validator = $this->createValidator();
        $validator->validate($extref, $constraint);
    }

    /**
     * Test validate() null value
     *
     * @return void
     */
    public function testValidateNull()
    {
        $url = null;
        $constraint = new ExtReference();

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
        $extref = 'not extref';
        $constraint = new ExtReference();

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->invalidMessage, ['%url%' => '']);

        $validator = $this->createValidator();
        $validator->validate($extref, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateNotAllowed()
    {
        $extref = ExtRef::create(__FILE__, __FUNCTION__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = [__CLASS__];

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($constraint->notAllowedMessage, ['%url%' => '']);

        $validator = $this->createValidator();
        $validator->validate($extref, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateAllowedEmpty()
    {
        $extref = ExtRef::create(__FILE__, __FUNCTION__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = null;

        $this->context->expects($this->never())
            ->method('addViolation');

        $validator = $this->createValidator();
        $validator->validate($extref, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateAllowedAll()
    {
        $extref = ExtRef::create(__FILE__, __FUNCTION__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = ['*'];

        $this->context->expects($this->never())
            ->method('addViolation');

        $validator = $this->createValidator();
        $validator->validate($extref, $constraint);
    }

    /**
     * Test validate()
     *
     * @return void
     */
    public function testValidateAllowedNeeded()
    {
        $extref = ExtRef::create(__FILE__, __FUNCTION__);

        $constraint = new ExtReference();
        $constraint->allowedCollections = [__FILE__];

        $this->context->expects($this->never())
            ->method('addViolation');

        $validator = $this->createValidator();
        $validator->validate($extref, $constraint);
    }
}
