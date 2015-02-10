<?php
/**
 * Class AuthenticationListenerTest
 *
 * PHP Version 5
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\SecurityBundle\EventListener;

use Graviton\SecurityBundle\EventListener\Strategies\StrategyCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class AuthenticationListenerTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test triggering of event
     *
     * @return void
     */
    public function testOnKernelRequest()
    {
        $server = array(
            'HTTP_X_IDP_USERNAMEINHALT' => "example-authentication-header",
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $strategy = $this->getMockBuilder('Graviton\SecurityBundle\EventListener\Strategies\StrategyInterface')
            ->getMockForAbstractClass();
        $strategy
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('\Graviton\SecurityBundle\EventListener\Strategies\StrategyInterface'));
        $strategy
            ->expects($this->once())
            ->method('apply')
            ->will($this->returnValue(array()));

        $strategyCollection = new StrategyCollection(array($strategy));

        $eventMock = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequestType', 'getRequest'))
            ->getMock();
        $eventMock
            ->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new AuthenticationListener($strategyCollection);
        $listener->onKernelRequest($eventMock);

        $this->assertEquals(
            array(
                '\Graviton\SecurityBundle\EventListener\Strategies\StrategyInterface' => array()
            ),
            $request->attributes->all()
        );
    }
}
