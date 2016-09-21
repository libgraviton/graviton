<?php
/**
 * Basic functional test for ActivityManager
 */
namespace Graviton\AuditTrackingBundle\Tests\Manager;

use Graviton\AuditTrackingBundle\Manager\ActivityManager;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ActivityManagerTest extends RestTestCase
{
    /** @var ActivityManager */
    private $activityManager;

    /**
     * Ensure a clean Db for test
     *
     * @return void
     */
    public function setUp()
    {
        $this->activityManager = $this->getContainer()->get('graviton.audit.manager.activity');
    }

    /**
     * Verifies the correct behavior of:
     * setConfiguration()
     * getConfigValue()
     *
     * @return void
     */
    public function testGetConfigValue()
    {
        $keys = [
            'bool_true' => true,
            'bool_false' => false,
            'int_1' => 1,
            'int' => 14,
            'string_a' => "simple string",
            'array_a' => ['item1', 'item2']
        ];

        $this->activityManager->setConfiguration($keys);

        foreach ($keys as $key => $val) {
            $type = explode('_', $key);
            $value = $this->activityManager->getConfigValue($key, $type[0]);
            $this->assertEquals($value, $val, 'Key '.$key.' was not handled as expected');
        }
    }

    /**
     * Verifies the correct behavior of:
     * extractHeaderLink()
     *
     * @return void
     */
    public function testGetHeader()
    {
        $method = $this->getPrivateClassMethod(get_class($this->activityManager), 'extractHeaderLink');

        // Double links
        $args = ['<http://localhost/core/app/bap>; rel="self",<http://localhost/core/app/bap/>; rel="next"', 'self'];
        $result = $method->invokeArgs($this->activityManager, $args);
        $this->assertEquals('http://localhost/core/app/bap', $result);

        // Simple link
        $args = ['<http://localhost/core/app/bap/>; rel="self"', 'self'];
        $result = $method->invokeArgs($this->activityManager, $args);
        $this->assertEquals('http://localhost/core/app/bap/', $result);

        // Triple links
        $args = [
            '<http://localhost/core/app/?limit(1%2C1)>; rel="next",'.
            '<http://localhost/core/app/?limit(1%2C2)>; rel="last",'.
            '<http://localhost/core/app/?limit(1)>; rel="self"',
            'self'];
        $result = $method->invokeArgs($this->activityManager, $args);
        $this->assertEquals('http://localhost/core/app/?limit(1)', $result);
    }
}
