<?php

namespace Graviton\SecurityBundle\Entities;

use Graviton\SecurityBundle\Tests\GravitonSecurityBundleTestCase;


class SecurityContractTest extends GravitonSecurityBundleTestCase
{
    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\GravitonDyn\ContractBundle\Document\Contract
     */
    protected function getContractMock(array $methods = array())
    {
        return $this->getMockBuilder('\GravitonDyn\ContractBundle\Document\Contract')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }


    public function testGetRoles()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertInternalType('array', $entity->getRoles());
    }

    public function testGetPassword()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertEmpty($entity->getPassword());
    }

    public function testGetSalt()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertEmpty($entity->getSalt());
    }

    public function testUsername()
    {
        $customerMock = $this->getMockBuilder('\GravitonDyn\CustomerBundle\Document\Customer')
            ->disableOriginalConstructor()
            ->setMethods(array('getFirstname', 'getLastname'))
            ->getMock();
        $customerMock
            ->expects($this->once())
            ->method('getFirstname')
            ->will($this->returnValue('Jon'));
        $customerMock
            ->expects($this->once())
            ->method('getLastname')
            ->will($this->returnValue('Doe'));

        $contractMock = $this->getContractMock(array('getCustomer'));
        $contractMock
            ->expects($this->exactly(2))
            ->method('getCustomer')
            ->will($this->returnValue($customerMock));

        $entity = new SecurityContract($contractMock);

        $this->assertEquals('Jon Doe', $entity->getUsername());
    }

    public function testEraeseCredentials()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertEmpty($entity->eraseCredentials());
    }


}
