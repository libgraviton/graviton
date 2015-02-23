<?php

namespace Graviton\SecurityBundle\Tests\DepedencyInjection\Compiler;

use Graviton\SecurityBundle\DependencyInjection\Compiler\AuthenticationKeyFinderPass;

/**
 * Class AuthenticationKeyFinderPassTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $taggedServiceIds = array(
            'myPersonalKeyFinderStrategy' => 'foo',
            'aNotRegisteredStrategy'      => 'bar'
        );

        $message = 'The service (aNotRegisteredStrategy) is not registered in the application kernel.';

        $loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->setMethods(array('warning'))
            ->getMockForAbstractClass();
        $loggerMock
            ->expects($this->once())
            ->method('warning')
            ->with($this->equalTo($message));

        $definitionMock = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->setMethods(array('addMethodCall', 'getDefinition'))
            ->getMock();
        $definitionMock
            ->expects($this->once())
            ->method('addMethodCall');

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('findTaggedServiceIds', 'getDefinition', 'hasDefinition'))
            ->getMock();
        $container
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('graviton.security.authenticationkey.finder'))
            ->will($this->returnValue($taggedServiceIds));
        $container
            ->expects($this->exactly(2))
            ->method('getDefinition')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($definitionMock),
                    $this->returnValue($loggerMock)
                )
            );
        $container
            ->expects($this->exactly(3))
            ->method('hasDefinition')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(true),
                    $this->returnValue(false),
                    $this->returnValue(true)
                )
            );

        $compilerPass = new AuthenticationKeyFinderPass();
        $compilerPass->process($container);
    }
}
