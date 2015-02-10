<?php

namespace Graviton\SecurityBundle\User;

/**
 * Class AirlockAuthenticationKeyUserProviderTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AirlockAuthenticationKeyUserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('\GravitonDyn\ContractBundle\Document\Contract')) {

            $this->markTestSkipped(
                'Mandatory generated class not available: \GravitonDyn\ContractBundle\Document\Contract'
            );
        }
    }


    public function testGetUsernameForApiKey()
    {
        $contractDocumentMock = $this->getMockBuilder('\GravitonDyn\ContractBundle\Document\Contract')
            ->setMethods(array('getId'))
            ->getMock();
        $contractDocumentMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('515616161648151'));

        $contractRepositoryMock = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->setMethods(array('findOneBy'))
            ->getMockForAbstractClass();
        $contractRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('number' => '51512011')))
            ->will($this->returnValue($contractDocumentMock));

        $contractModelMock = $this->getContractModelMock(array('getRepository'));
        $contractModelMock
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($contractRepositoryMock));

        $provider = new AirlockAuthenticationKeyUserProvider($contractModelMock);

        $this->assertSame('515616161648151', $provider->getUsernameForApiKey('51512011'));
    }

    public function testLoadUserByUsername()
    {
        $contractDocumentMock = $this->getMock('\GravitonDyn\ContractBundle\Document\Contract');

        $contractModelMock = $this->getContractModelMock(array('find'));
        $contractModelMock
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($contractDocumentMock));

        $provider = new AirlockAuthenticationKeyUserProvider($contractModelMock);

        $this->isInstanceOf(
            '\Symfony\Component\Security\Core\User\UserInterface',
            $provider->loadUserByUsername('Tux')
        );
    }

    public function testGetUserByNameExpectingException()
    {
        $contractModelMock = $this->getContractModelMock(array('find'));
        $contractModelMock
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));

        $provider = new AirlockAuthenticationKeyUserProvider($contractModelMock);

        $this->setExpectedException('\Symfony\Component\Security\Core\Exception\UsernameNotFoundException');

        $provider->loadUserByUsername('515616161648151');
    }

    public function testRefreshUser()
    {
        $provider = new AirlockAuthenticationKeyUserProvider($this->getContractModelMock());

        $this->setExpectedException('\Symfony\Component\Security\Core\Exception\UnsupportedUserException');

        $provider->refreshUser($this->getUserMock());

    }

    public function testSupportsClass()
    {
        $provider = new AirlockAuthenticationKeyUserProvider($this->getContractModelMock());

        $this->assertTrue($provider->supportsClass($this->getUserMock()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\User\UserInterface
     */
    private function getUserMock()
    {
        $userMock = $this->getMockBuilder('\Symfony\Component\Security\Core\User\UserInterface')
            ->getMockForAbstractClass();
        return $userMock;
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\GravitonDyn\ContractBundle\Model\Contract
     */
    private function getContractModelMock(array $methods = array())
    {
        $userMock = $this->getMockBuilder('\GravitonDyn\ContractBundle\Model\Contract')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        return $userMock;
    }
}
