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
     *
     * @return void
     */
    public function testSupportsAttribute()
    {
        $voter = new OwnContextVoter();

        $this->assertTrue($voter->supportsAttribute('VIEW'));
    }

    /**
     * validate supportsClass
     *
     * @return void
     */
    public function testSupportsClass()
    {
        $voter = new OwnContextVoter();

        $this->assertFalse($voter->supportsClass('\stdClass'));
    }

    /**
     * validates isGranted
     *
     * @dataProvider invalidUserProvider
     *
     * @param object|null $user The user asking for permission.
     *
     * @return void
     */
    public function testIsGrantedNoValidUser($user)
    {
        $attribute = 'VIEW';
        $object = new \stdClass();

        $voter = $this->getVoterProxy(array('isGranted'));

        $this->assertFalse($voter->isGranted($attribute, $object, $user));
    }

    /**
     * @return array
     */
    public function invalidUserProvider()
    {
        return array(
            'null user' => array(null),
            'some user object' => array(new \stdClass())
        );
    }

    /**
     * validates isGranted
     *
     * @return void
     */
    public function testIsGranted()
    {
        $object = new \stdClass();

        $contractDouble = $this->getContractDouble(array('getAccount', 'getCustomer'));
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

        $voter = $this->getVoterProxy(array('isGranted'));

        $this->assertFalse($voter->isGranted('VIEW', $object, $userDouble));
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

        $voter = $this->getVoterProxy(array('grantByAccount'));

        $this->assertFalse($voter->grantByAccount($contractDouble, new \stdClass));
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

        $voter = $this->getVoterProxy(array('grantByAccount'));

        $this->assertTrue($voter->grantByAccount($contractDouble, $accountDouble));
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

        $voter = $this->getVoterProxy(array('grantByCustomer'));

        $this->assertFalse($voter->grantByCustomer($contractDouble, new \stdClass));
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

        $voter = $this->getVoterProxy(array('grantByCustomer'));

        $this->assertTrue($voter->grantByCustomer($contractDouble, $customerDouble));
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
     * Provides a proxy instance of the OwnContextVoter.
     *
     * @param array $methods Set of methods to be doubled.
     *
     * @return object
     */
    private function getVoterProxy(array $methods = array())
    {
        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\OwnContextVoter')
            ->setMethods($methods)
            ->getProxy();
        return $voter;
    }
}
