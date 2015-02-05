<?php

namespace Graviton\SecurityBundle\User;


class AirlockAuthenticationKeyUserProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetUsernameForApiKey()
    {
        $provider = new AirlockAuthenticationKeyUserProvider();

        $this->assertSame('Tux', $provider->getUsernameForApiKey('mySpecialApiKey'));
    }

    public function testLoadUserByUsername()
    {
        $provider = new AirlockAuthenticationKeyUserProvider();

        $this->isInstanceOf('\Symfony\Component\Security\Core\User\UserInterface', $provider->loadUserByUsername('Tux'));
    }

    public function testRefreshUser()
    {
        $provider = new AirlockAuthenticationKeyUserProvider();

        $this->setExpectedException('\Symfony\Component\Security\Core\Exception\UnsupportedUserException');

        $provider->refreshUser($this->getUserMock());

    }

    public function testSupportsClass()
    {
        $provider = new AirlockAuthenticationKeyUserProvider();

        $this->assertTrue($provider->supportsClass($this->getUserMock()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserMock()
    {
        $userMock = $this->getMockBuilder('\Symfony\Component\Security\Core\User\UserInterface')
            ->getMockForAbstractClass();
        return $userMock;
    }
}
