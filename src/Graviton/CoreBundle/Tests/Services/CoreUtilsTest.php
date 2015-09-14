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
    private $versions = array(
                array("id" => "graviton", "version" => "0.25.1", "isWrapper" => true),
                array("id" => "financing", "version" => "0.1", "isWrapper" => false),
            );
    /**
     * Verifies the correct behavior of getVersion()
     *
     * @return void
     */
    public function testGetVersionById()
    {
        $utils = new CoreUtils($this->versions);
        $this->assertEquals($this->versions[0]['version'], $utils->getVersionById('graviton')['version']);
    }

    /**
     * Verifies the correct behavior of getWrapperVersion()
     *
     * @return void
     */
    public function testGetWrapperVersion()
    {
        $utils = new CoreUtils($this->versions);
        $this->assertEquals(
            array("id" => "graviton", "version" => "0.25.1", "isWrapper" => true),
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
            'graviton: 0.25.1; financing: 0.1; ',
            $utils->getVersionInHeaderFormat()
        );
        $this->assertInternalType('string', $utils->getVersionInHeaderFormat());
    }
}
