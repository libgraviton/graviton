<?php
/**
 * Basic functional test for static util class
 */

namespace Graviton\CoreBundle\Service;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CoreUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $versions = [
        array("id" => "self", "version" => "0.25.1"),
        array("id" => "financing", "version" => "0.1")
    ];

    /**
     * Verifies the correct behavior of getWrapperVersion()
     *
     * @return void
     */
    public function testGetWrapperVersion()
    {
        $utils = new CoreUtils($this->versions);
        $this->assertEquals(
            array("id" => "self", "version" => "0.25.1"),
            $utils->getWrapperVersion()
        );
        $this->assertInternalType('array', $utils->getWrapperVersion());
    }

    /**
     * Verifies the correct behavior of getVersionInHeaderFormat()
     *
     * @return void
     */
    public function testGetVersionInHeaderFormat()
    {
        $utils = new CoreUtils($this->versions);
        $this->assertEquals(
            'self: 0.25.1; financing: 0.1; ',
            $utils->getVersionInHeaderFormat()
        );
        $this->assertInternalType('string', $utils->getVersionInHeaderFormat());
    }
}
