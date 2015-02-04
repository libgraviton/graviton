<?php

namespace Graviton\SecurityBundle\Authentication;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AirlockAuthenticationKeyAuthenticatorTest
 *
 * @package Graviton\SecurityBundle\Authentication
 */
class AirlockAuthenticationKeyAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider stringProvider
     */
    public function testCreateToken($headerFieldValue)
    {
        $securityUserMock =  $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMockForAbstractClass();

        $userProviderMock = $this->getMockBuilder('Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('getUsernameForApiKey', 'loadUserByUsername'))
            ->getMock();

        $userProviderMock
            ->expects($this->once())
            ->method('getUsernameForApiKey')
            ->with($this->equalTo($headerFieldValue))
            ->will($this->returnValue('tux'));
        $userProviderMock
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->will($this->returnValue($securityUserMock));


        $authenticator = new AirlockAuthenticationKeyAuthenticator($userProviderMock);

        $server = array(
            'HTTP_X_IDP_USERNAMEINHALT' => $headerFieldValue, //"example-authentication-header",
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $token = $authenticator->createToken($request, 'AirlockProviderKey');

        $this->assertInstanceOf(
            '\Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken',
            $token
        );

        $this->assertTrue($token->isAuthenticated());
    }

    public function stringProvider()
    {
        return array(
            'plain string, no special chars' => array('exampleAuthenticationHeader'),
            'string with special chars' => array("$-_.+!*'(),{}|\\^~[]`<>#%;/?:@&=."),
            'string with octal chars' => array("a: \141, A: \101"),
            'string with hex chars' => array("a: \x61, A: \x41"),
            'live example' => array("x-idp-10N0000188"),
        );
    }

}
