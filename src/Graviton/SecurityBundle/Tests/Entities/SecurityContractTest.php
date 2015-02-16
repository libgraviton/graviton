<?php
/**
 * test security contract (mainly getters and defaults)
 */

namespace Graviton\SecurityBundle\Entities;

use Graviton\SecurityBundle\Tests\GravitonSecurityBundleTestCase;

/**
 * Class SecurityContractTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityContractTest extends GravitonSecurityBundleTestCase
{
    /**
     * @param string[] $methods methods to mock
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

    /**
     * roles should always return an array
     *
     * @return void
     */
    public function testGetRoles()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertInternalType('array', $entity->getRoles());
    }

    /**
     * get password should return empty
     *
     * @return void
     */
    public function testGetPassword()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertEmpty($entity->getPassword());
    }

    /**
     * get salt should return empty
     *
     * @return void
     */
    public function testGetSalt()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertEmpty($entity->getSalt());
    }

    /**
     * test getting username from contract
     *
     * @return void
     */
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

    /**
     * test credential removal
     *
     * @return void
     */
    public function testEraeseCredentials()
    {
        $entity = new SecurityContract($this->getContractMock());

        $this->assertEmpty($entity->eraseCredentials());
    }
}
