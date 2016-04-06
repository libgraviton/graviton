<?php
/**
 * Validates the behavior of the AuthenticationLogger event listener.
 */
namespace Graviton\SecurityBundle\Tests\Listener;

use Graviton\SecurityBundle\Listener\AuthenticationLogger;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationLoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject logger */
    private $logger;

    /**
     * @return void
     */
    protected function setUp()
    {
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject logger */
        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->setMethods(array('warning', 'info'))
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testOnAuthenticationFailure()
    {
        $exceptionDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Exception\AuthenticationException')
            ->disableOriginalConstructor()
            ->setMethods(array('getMessageKey'))
            ->getMock();
        $exceptionDouble
            ->expects($this->once())
            ->method('getMessageKey')
            ->will($this->returnValue('An authentication exception occurred.'));

        $eventDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Event\AuthenticationFailureEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getAuthenticationException'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getAuthenticationException')
            ->willReturn($exceptionDouble);

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('An authentication exception occurred.'),
                $this->isType('array')
            );

        $logger = new AuthenticationLogger($this->logger);
        $logger->onAuthenticationFailure($eventDouble);
    }

    /**
     * @return void
     */
    public function testOnAuthenticationSuccess()
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->equalTo('Entity (Jon Doe) was successfully recognized.'));

        $tokenDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->setMethods(array('getUsername'))
            ->getMockForAbstractClass();
        $tokenDouble
            ->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('Jon Doe'));

        $eventDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Event\AuthenticationEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getAuthenticationToken'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn($tokenDouble);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Entity (Jon Doe) was successfully recognized.')
            );

        $logger = new AuthenticationLogger($this->logger);

        $logger->onAuthenticationSuccess($eventDouble);
    }

    /**
     * @return void
     */
    public function testOnAuthenticationSuccessNonContract()
    {
        $tokenDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
                            ->setMethods(array('getUser'))
                            ->getMockForAbstractClass();
        $tokenDouble
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn('johnny');

        $eventDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Event\AuthenticationEvent')
                            ->disableOriginalConstructor()
                            ->setMethods(array('getAuthenticationToken'))
                            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn($tokenDouble);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Entity (johnny) was successfully recognized.')
            );

        $logger = new AuthenticationLogger($this->logger);

        $logger->onAuthenticationSuccess($eventDouble);
    }
}
