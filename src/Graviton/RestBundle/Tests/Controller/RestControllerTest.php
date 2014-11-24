<?php

namespace Graviton\RestBundle\Tests\Controller;

/**
 * Tests RestController.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Bastian Feder <lapistano@bastian-feder.de>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Verifies that the SUT throws a specific exception, in case a validation fails.
     *
     * @return void
     */
    public function testValidateRecordExpectingException()
    {
        $constraintViolationListMock =
            $this->getMockBuilder('\Symfony\Component\Validator\ConstraintViolationListInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('count'))
            ->getMockForAbstractClass();
        $constraintViolationListMock
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $validatorMock = $this->getMockBuilder('\Symfony\Component\Validator\ValidatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('validate'))
            ->getMockForAbstractClass();
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationListMock));

        $containerMock = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $containerMock
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('graviton.rest.validator'))
            ->will($this->returnValue($validatorMock));
        $containerMock
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('graviton.rest.response'));

        $record = $this->getMock('\Graviton\CoreBundle\Document\App');

        $controller = new RestControllerProxy();
        $controller->setContainer($containerMock);

        $this->setExpectedException('\Graviton\ExceptionBundle\Exception\ValidationException');

        $controller->validateRecord($record);
    }

    /**
     * Verifies that the SUT does not return any value.
     *
     * @return void
     */
    public function testValidateRecord()
    {
        $constraintViolationListMock =
            $this->getMockBuilder('\Symfony\Component\Validator\ConstraintViolationListInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('count'))
            ->getMockForAbstractClass();
        $constraintViolationListMock
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $validatorMock = $this->getMockBuilder('\Symfony\Component\Validator\ValidatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('validate'))
            ->getMockForAbstractClass();
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationListMock));

        $containerMock = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $containerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('graviton.rest.validator'))
            ->will($this->returnValue($validatorMock));

        $record = $this->getMock('\Graviton\CoreBundle\Document\App');

        $controller = new RestControllerProxy();
        $controller->setContainer($containerMock);

        $this->assertNull($controller->validateRecord($record));
    }
}
