<?php
/**
 * main checks for airlock authenticator
 */

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface;
use Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
 * Class AirlockAuthenticationKeyAuthenticatorTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AirlockAuthenticationKeyAuthenticatorTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider stringProvider
     *
     * @param string $headerFieldValue value to check with
     *
     * @return void
     */
    public function testCreateToken($headerFieldValue)
    {
        $userProviderMock = $this->getMockBuilder('Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('getUsernameForApiKey', 'loadUserByUsername'))
            ->getMock();

        $strategy = $this->getMockBuilder('\Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface')
            ->setMethods(array('apply'))
            ->getMockForAbstractClass();
        $strategy
            ->expects($this->once())
            ->method('apply')
            ->will($this->returnValue($headerFieldValue));

        $authenticator = new AirlockAuthenticationKeyAuthenticator($userProviderMock, $strategy, $this->logger);

        $server = array(
            'HTTP_X_IDP_USERNAME' => $headerFieldValue, //"example-authentication-header",
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $token = $authenticator->createToken($request, 'AirlockProviderKey');

        $this->assertInstanceOf(
            '\Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken',
            $token
        );

        $this->assertFalse($token->isAuthenticated());
    }

    /**
     * @return array<string>
     */
    public function stringProvider()
    {
        return array(
            'plain string, no special chars' => array('exampleAuthenticationHeader'),
            'string with special chars' => array("$-_.+!*'(),{}|\\^~[]`<>#%;/?:@&=."),
            'string with octal chars' => array("a: \141, A: \101"),
            'string with hex chars' => array("a: \x61, A: \x41"),
            'live example' => array("10N0000188"),
        );
    }

    /**
     * @return void
     */
    public function testAuthenticateToken()
    {
        $providerKey = 'some providerKey';
        $apiKey = 'exampleAuthenticationHeader';

        $securityUserMock =  $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->setMethods(array('getRoles'))
            ->getMockForAbstractClass();
        $securityUserMock
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array('ROLE_GRAVITON_USER')));

        $userProviderMock = $this->getProviderMock(array('getUsernameForApiKey', 'loadUserByUsername'));
        $userProviderMock
            ->expects($this->once())
            ->method('getUsernameForApiKey')
            ->with($this->equalTo($apiKey))
            ->will($this->returnValue('Tux'));
        $userProviderMock
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->will($this->returnValue($securityUserMock));

        $anonymousToken = new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );

        $authenticator = new AirlockAuthenticationKeyAuthenticator(
            $userProviderMock,
            $this->getStrategyMock(),
            $this->logger
        );

        $token = $authenticator->authenticateToken($anonymousToken, $userProviderMock, $providerKey);

        $this->assertInstanceOf(
            '\Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken',
            $token
        );

        $this->assertTrue($token->isAuthenticated());
    }

    /**
     * @return void
     */
    public function testAuthenticateTokenExpectingException()
    {
        $providerKey = 'some providerKey';
        $apiKey = 'exampleAuthenticationHeader';

        $userProviderMock = $this->getProviderMock(array('getUsernameForApiKey', 'loadUserByUsername'));
        $userProviderMock
            ->expects($this->once())
            ->method('getUsernameForApiKey')
            ->with($this->equalTo($apiKey))
            ->will($this->returnValue(''));

        $anonymousToken = new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );

        $authenticator = new AirlockAuthenticationKeyAuthenticator(
            $userProviderMock,
            $this->getStrategyMock(),
            $this->logger
        );

        $this->setExpectedException('\Symfony\Component\Security\Core\Exception\AuthenticationException');

        $authenticator->authenticateToken($anonymousToken, $userProviderMock, $providerKey);
    }

    /**
     * @return void
     */
    public function testSupportsToken()
    {
        $providerKey = 'some providerKey';
        $apiKey = 'exampleAuthenticationHeader';

        $anonymousToken = new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );

        $authenticator = new AirlockAuthenticationKeyAuthenticator(
            $this->getProviderMock(),
            $this->getStrategyMock(),
            $this->logger
        );

        $this->assertTrue($authenticator->supportsToken($anonymousToken, $providerKey));
    }

    /**
     * @return void
     */
    public function testOnAuthenticationFailure()
    {
        $exceptionMock = $this->getMockBuilder('\Symfony\Component\Security\Core\Exception\AuthenticationException')
            ->disableOriginalConstructor()
            ->setMethods(array('getMessageKey'))
            ->getMock();
        $exceptionMock
            ->expects($this->exactly(2))
            ->method('getMessageKey')
            ->will($this->returnValue('test_message'));

        $authenticator = new AirlockAuthenticationKeyAuthenticator(
            $this->getProviderMock(),
            $this->getStrategyMock(),
            $this->logger
        );

        $response = $authenticator->onAuthenticationFailure(new Request(), $exceptionMock);

        $this->assertEquals('test_message', $response->getContent());
        $this->assertEquals(511, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testOnAuthenticationSuccess()
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->equalTo('Contract (Jon Doe (1234567)) was successfully recognized.'));

        $authenticator = new AirlockAuthenticationKeyAuthenticator(
            $this->getProviderMock(),
            $this->getStrategyMock(),
            $this->logger
        );

        $userDouble = $this->getMockBuilder('\Graviton\SecurityBundle\Entities\SecurityContract')
            ->disableOriginalConstructor()
            ->setMethods(array('getContractNumber'))
            ->getMock();
        $userDouble
            ->expects($this->once())
            ->method('getContractNumber')
            ->will($this->returnValue('1234567'));

        $tokenDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->setMethods(array('getUsername', 'getUser'))
            ->getMockForAbstractClass();
        $tokenDouble
            ->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('Jon Doe'));
        $tokenDouble
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($userDouble));

        $authenticator->onAuthenticationSuccess(new Request(), $tokenDouble);
    }

    /**
     * @param string[] $methods methods to mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AirlockAuthenticationKeyUserProvider
     */
    private function getProviderMock(array $methods = array())
    {
        $userProviderMock = $this->getMockBuilder('Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        return $userProviderMock;
    }

    /**
     * @param array $methods methods to mock
     *
     * @return StrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStrategyMock(array $methods = array('apply'))
    {
        return $this->getMockBuilder('\Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface')
            ->setMethods($methods)
            ->getMockForAbstractClass();
    }
}
