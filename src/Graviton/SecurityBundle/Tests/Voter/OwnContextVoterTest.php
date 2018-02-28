<?php
/**
 * Validates the correct behavior of the voter
 */
namespace Graviton\SecurityBundle\Voter;

use Doctrine\Common\Collections\ArrayCollection;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class OwnContextVoterTest extends GravitonTestCase
{

    /**
     * validates isGranted
     *
     * @return void
     */
    public function testIsGrantedNoValidUser()
    {
        $attribute = 'VIEW';
        $object = new \stdClass();

        $token = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->getMock();

        $voter = $this->createMock('\Graviton\SecurityBundle\Voter\OwnContextVoter');
        $voterMethod = $this->getVoterProxyMethod('voteOnAttribute');

        $this->assertFalse($voterMethod->invokeArgs($voter, [$attribute, $object, $token]));
    }

    /**
     * validates isGranted
     *
     * @return void
     */
    public function testIsGranted()
    {
        $object = new \stdClass();

        $contractDouble = $this->getContractDouble(['getAccount', 'getCustomer']);
        $contractDouble
            ->expects($this->any())
            ->method('getAccount')
            ->willReturn(new ArrayCollection([$object]));
        $contractDouble
            ->expects($this->any())
            ->method('getCustomer')
            ->willReturn($object);

        $userDouble = $this->getSimpleTestDouble(
            '\Graviton\SecurityBundle\Entities\SecurityContract',
            array('getContract')
        );
        $userDouble
            ->expects($this->once())
            ->method('getContract')
            ->willReturn($contractDouble);

        $token = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($userDouble);

        $voter = $this->createMock('\Graviton\SecurityBundle\Voter\OwnContextVoter');
        $voterMethod = $this->getVoterProxyMethod('voteOnAttribute');

        $this->assertFalse($voterMethod->invokeArgs($voter, ['VIEW', $object, $token]));
    }

    /**
     * verifies grandByAccount
     *
     * @return void
     */
    public function testGrantByAccountGrandAccess()
    {
        $contractDouble = $this->getContractDouble(array('getAccount'));
        $contractDouble
            ->expects($this->any())
            ->method('getAccount')
            ->willReturn(new ArrayCollection([new \stdClass]));

        $voter = $this->createMock('\Graviton\SecurityBundle\Voter\OwnContextVoter');
        $voterMethod = $this->getVoterProxyMethod('grantByAccount');

        $this->assertFalse($voterMethod->invokeArgs($voter, [$contractDouble, new \stdClass]));
    }

    /**
     * verifies grandByAccount
     *
     * @return void
     */
    public function testGrantByAccountDenyAccess()
    {
        $accountDouble = $this->getMockBuilder('\GravitonDyn\AccountBundle\Document\Account')
            ->disableOriginalConstructor()
            ->getMock();

        $contractDouble = $this->getContractDouble(array('getAccount'));
        $contractDouble
            ->expects($this->any())
            ->method('getAccount')
            ->willReturn(new ArrayCollection([$accountDouble]));

        $voter = $this->createMock('\Graviton\SecurityBundle\Voter\OwnContextVoter');
        $voterMethod = $this->getVoterProxyMethod('grantByAccount');

        $this->assertTrue($voterMethod->invokeArgs($voter, [$contractDouble, $accountDouble]));
    }

    /**
     * verifies grantByCustomer
     *
     * @return void
     */
    public function testGrantByCustomerGrandAccess()
    {
        $contractDouble = $this->getContractDouble(array('getCustomer'));
        $contractDouble
            ->expects($this->any())
            ->method('getCustomer')
            ->willReturn(new \stdClass);

        $voter = $this->createMock('\Graviton\SecurityBundle\Voter\OwnContextVoter');
        $voterMethod = $this->getVoterProxyMethod('grantByCustomer');

        $this->assertFalse($voterMethod->invokeArgs($voter, [$contractDouble, new \stdClass]));
    }

    /**
     * verifies grantByCustomer
     *
     * @return void
     */
    public function testGrantByCustomerDenyAccess()
    {
        $customerDouble = $this->getMockBuilder('\GravitonDyn\CustomerBundle\Document\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $contractDouble = $this->getContractDouble(array('getCustomer'));
        $contractDouble
            ->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerDouble);

        $voter = $this->createMock('\Graviton\SecurityBundle\Voter\OwnContextVoter');
        $voterMethod = $this->getVoterProxyMethod('grantByCustomer');

        $this->assertTrue($voterMethod->invokeArgs($voter, [$contractDouble, $customerDouble]));
    }

    /**
     * Provides test double of the Contract entity.
     *
     * @param array $methods Set of methods to be doubled.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\GravitonDyn\ContractBundle\Document\Contract
     */
    public function getContractDouble(array $methods = array())
    {
        return $this->getSimpleTestDouble('\GravitonDyn\ContractBundle\Document\Contract', $methods);
    }

    /**
     * Provides a protected function.
     *
     * @param string $method method
     *
     * @return object
     */
    private function getVoterProxyMethod($method)
    {
        return $this->getPrivateClassMethod('\Graviton\SecurityBundle\Voter\OwnContextVoter', $method);
    }
}
