<?php
/**
 * Validates the behavior of the AuthenticationLogger event listener.
 */
namespace Graviton\SecurityBundle\Tests\Listener;

use Graviton\SecurityBundle\Service\SecurityUtils;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityUtilsTest extends RestTestCase
{
    /**
     * @var SecurityUtils
     */
    private $securityUtils;


    /**
     * Start up the test
     *
     * @return void
     */
    public function setUp()
    {
        $this->securityUtils = $this->getContainer()->get('graviton.security.service.utils');
    }

    /**
     * Test correct generation of UUID
     *
     * @return void
     */
    public function testGenerateUuid()
    {
        $method = $this->getPrivateClassMethod(get_class($this->securityUtils), 'generateUuid');
        $resultA = $method->invokeArgs($this->securityUtils, []);
        $this->assertRegExp('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $resultA);

        $resultB = $method->invokeArgs($this->securityUtils, []);
        $this->assertRegExp('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $resultB);

        $resultC = $method->invokeArgs($this->securityUtils, []);
        $this->assertRegExp('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $resultC);
        
        $this->assertNotEquals($resultA, $resultB);
        $this->assertNotEquals($resultA, $resultC);
        $this->assertNotEquals($resultB, $resultC);
    }

    /**
     * Test that UUID is the same on each request
     * We do not make a real request, just the function
     *
     * @return void
     */
    public function testGetRequestId()
    {
        $resultA = $this->securityUtils->getRequestId();
        $resultB = $this->securityUtils->getRequestId();

        $this->assertRegExp('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $resultA);
        $this->assertEquals($resultA, $resultB);
    }
}
