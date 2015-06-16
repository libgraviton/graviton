<?php
/**
 * Tests RestController.
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\RestBundle\Controller\RestController;

/**
 * Tests RestController.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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

        $validatorMock = $this->getMockBuilder('\Symfony\Component\Validator\Validator\ValidatorInterface')
            ->setMethods(array('validate'))
            ->getMockForAbstractClass();
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationListMock));

        $record = $this->getMock('\Graviton\CoreBundle\Document\App');

        $controller = $this->getRestControllerProxy($validatorMock);

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

        $validatorMock = $this->getMockBuilder('\Symfony\Component\Validator\Validator\ValidatorInterface')
            ->setMethods(array('validate'))
            ->getMockForAbstractClass();
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($constraintViolationListMock));

        $record = $this->getMock('\Graviton\CoreBundle\Document\App');

        $controller = $this->getRestControllerProxy($validatorMock);

        $this->assertNull($controller->validateRecord($record));
    }

    /**
     * Get a RestControllerProxy
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $validatorMock Mock of a ValidatorInterface
     *
     * @return RestControllerProxy
     */
    public function getRestControllerProxy($validatorMock)
    {
        $controller = new RestControllerProxy(
            $this
                ->getMock('\Symfony\Component\HttpFoundation\Response'),
            $this
                ->getMock('\Graviton\RestBundle\Service\RestUtilsInterface'),
            $this
                ->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\Router')
                ->disableOriginalConstructor()
                ->getMock(),
            $this
                ->getMockBuilder('\Graviton\I18nBundle\Repository\LanguageRepository')
                ->disableOriginalConstructor()
                ->getMock(),
            $validatorMock,
            $this
                ->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
                ->disableOriginalConstructor()
                ->getMock(),
            $this
                ->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
                ->disableOriginalConstructor()
                ->getMock()
        );
        return $controller;
    }
}
