<?php
/**
 * Class AuthenticationKeyFinderPassTest
 */

namespace Graviton\SecurityBundle\Tests\DepedencyInjection\Compiler;

use Graviton\SecurityBundle\DependencyInjection\Compiler\AuthenticationKeyFinderPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test process method
     *
     * @return void
     */
    public function testProcess()
    {
        $message = 'The service (strategy.doesnotExists) is not registered in the application kernel.';

        $loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->setMethods(array('warning'))
            ->getMockForAbstractClass();
        $loggerMock
            ->expects($this->once())
            ->method('warning')
            ->with($this->equalTo($message));

        $definitionMock = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(
                [
                    'findTaggedServiceIds',
                    'getDefinition',
                    'getParameter',
                    'hasParameter',
                    'findDefinition',
                    'hasDefinition'
                ]
            )
            ->getMock();
        $container
            ->expects($this->exactly(1))
            ->method('findDefinition')
            ->will($this->returnValue($definitionMock));
        $container
            ->expects($this->exactly(1))
            ->method('hasParameter')
            ->will($this->returnValue(true));
        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue('graviton.security.authentication.strategy.multi'),
                    $this->returnValue(
                        [
                            'graviton.security.authentication.strategy.subnet',
                            'graviton.security.authentication.strategy.header',
                            'graviton.security.authentication.strategy.cookie',
                            'strategy.doesnotExists'
                        ]
                    )
                )
            );
        $container
            ->expects($this->exactly(1))
            ->method('getDefinition')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($loggerMock)
                )
            );
        $container
            ->expects($this->exactly(5))
            ->method('hasDefinition')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(true), // service id
                    $this->returnValue(true), // service id
                    $this->returnValue(true), // service id
                    $this->returnValue(false), // not exists service id
                    $this->returnValue(true) // logger service id
                )
            );

        $compilerPass = new AuthenticationKeyFinderPass();
        $compilerPass->process($container);
    }
}
