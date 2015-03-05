<?php
/**
 * Validates the correct behavior of the voter
 */
namespace Graviton\SecurityBundle\Voter;

use Doctrine\Common\Collections\ArrayCollection;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class OwnContextVoterTest extends GravitonTestCase
{
    /**
     * validate supportsAttribute
     */
    public function testSupportsAttribute()
    {
        $voter = new OwnContextVoter();

        $this->assertTrue($voter->supportsAttribute('view'));
    }

    /**
     * validate supportsClass
     */
    public function testSupportsClass()
    {
        $voter = new OwnContextVoter();

        $this->assertTrue($voter->supportsClass('\stdClass'));
    }

    /**
     * validates vote
     */
    public function testVote()
    {
        $object = new \stdClass();

        $contractDouble = $this->getContractDouble(array('getCustomer'));
        $contractDouble
            ->expects($this->any())
            ->method('getCustomer')
            ->willReturn($object);

        $userDouble = $this->getMockBuilder('\Graviton\SecurityBundle\Entities\SecurityContract')
            ->disableOriginalConstructor()
            ->setMethods(array('getContract'))
            ->getMock();
        $userDouble
            ->expects($this->once())
            ->method('getContract')
            ->willReturn($contractDouble);

        $tokenDouble = $this->getMockBuilder('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->setMethods(array('getUser'))
            ->getMockForAbstractClass();
        $tokenDouble
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($userDouble);

        $voter = new OwnContextVoter();

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($tokenDouble, $object, array()));
    }

    /**
     * verifies grandByAccount
     */
    public function testGrantByAccountGrandAccess()
    {
        $contractDouble = $this->getContractDouble(array('getAccount'));
        $contractDouble
            ->expects($this->any())
            ->method('getAccount')
            ->willReturn(new ArrayCollection([new \stdClass]));

        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\OwnContextVoter')
            ->setMethods(array('grantByAccount'))
            ->getProxy();

        $this->assertFalse($voter->grantByAccount($contractDouble, new \stdClass));
    }

    /**
     * verifies grandByAccount
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

        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\OwnContextVoter')
            ->setMethods(array('grantByAccount'))
            ->getProxy();

        $this->assertTrue($voter->grantByAccount($contractDouble, $accountDouble));
    }

    /**
     * verifies grantByCustomer
     */
    public function testGrantByCustomerGrandAccess()
    {
        $contractDouble = $this->getContractDouble(array('getCustomer'));
        $contractDouble
            ->expects($this->any())
            ->method('getCustomer')
            ->willReturn(new \stdClass);

        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\OwnContextVoter')
            ->setMethods(array('grantByCustomer'))
            ->getProxy();

        $this->assertFalse($voter->grantByCustomer($contractDouble, new \stdClass));
    }

    /**
     * verifies grantByCustomer
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

        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\OwnContextVoter')
            ->setMethods(array('grantByCustomer'))
            ->getProxy();

        $this->assertTrue($voter->grantByCustomer($contractDouble, $customerDouble));
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\GravitonDyn\ContractBundle\Document\Contract
     */
    public function getContractDouble(array $methods = array())
    {
        return $this->getSimpleTestDouble('\GravitonDyn\ContractBundle\Document\Contract', $methods);
    }
}
