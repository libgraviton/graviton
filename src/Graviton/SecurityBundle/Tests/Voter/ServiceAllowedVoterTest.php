<?php
/**
 * Validates the correct behavior of the voter
 */
namespace Graviton\SecurityBundle\Voter;

use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ServiceAllowedVoterTest extends GravitonTestCase
{
    /** @var array  */
    private $whitelist = array();

    /**
     * Test setup
     *
     * @return void
     */
    protected function setup()
    {
        $this->whitelist = array('/app/core');
    }

    /**
     * verifies isGranted()
     *
     * @return void
     */
    public function testIsGranted()
    {
        $request = $this->getSimpleTestDouble('\Symfony\Component\HttpFoundation\Request', array('getPathInfo'));
        $request
            ->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/app/core');

        $voter = $this->getMockBuilder()
            ->setConstructorArgs([$this->whitelist])
            ->getMock();
        $protectedMethod = $this->getPrivateClassMethod($voter, 'voteOnAttribute');

        $token = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->getMock();

        $this->assertTrue($protectedMethod->invokeArgs($voter, ['VIEW', $request, $token]));
    }
}
