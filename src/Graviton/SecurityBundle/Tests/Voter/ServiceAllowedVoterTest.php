<?php
/**
 * Validates the correct behavior of the voter
 */
namespace Graviton\SecurityBundle\Voter;

use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * verifies getSupportedAttributes()
     *
     * @return void
     */
    public function testGetSupportedAttributes()
    {
        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\ServiceAllowedVoter')
            ->setConstructorArgs(array($this->whitelist))
            ->setMethods(array('getSupportedAttributes'))
            ->getProxy();

        $this->assertContains('VIEW', $voter->getSupportedAttributes());
    }

    /**
     * verifies getSupportedAttributes()
     *
     * @return void
     */
    public function testGetSupportedClasses()
    {
        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\ServiceAllowedVoter')
            ->setConstructorArgs(array($this->whitelist))
            ->setMethods(array('getSupportedClasses'))
            ->getProxy();

        $this->assertContains(
            'Symfony\Component\HttpFoundation\Request',
            $voter->getSupportedClasses()
        );
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

        $voter = $this->getProxyBuilder('\Graviton\SecurityBundle\Voter\ServiceAllowedVoter')
            ->setConstructorArgs(array($this->whitelist))
            ->setMethods(array('isGranted'))
            ->getProxy();

        $this->assertTrue($voter->isGranted('VIEW', $request));
    }
}
