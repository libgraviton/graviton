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
     * Verifies the correct behavior of getVersion()
     *
     * @dataProvider versionAndFileProvider
     *
     * @param array $version Version array to be used
     *
     * @return void
     */
    public function testGetVersion($version)
    {
        $utils = new CoreUtils($version);
        $this->assertEquals($version[0]['version'], $utils->getVersionById('graviton')['version']);
    }

    /**
     * Provides test sets for the getVersion() test.
     *
     * @return array
     */
    public function versionAndFileProvider()
    {
        return array(
            'get from default file' => array(array(array("id" => "graviton", "version" => "0.25.1"))),
        );
    }
    //
    //    /**
    //     * Verifies the correct behavior of getVersion()
    //     *
    //     * @return void
    //     */
    //    public function testGetDefaultVersion()
    //    {
    //        $utils = new CoreUtils(array(array("id" => "graviton", "version" => "0.25.1")));
    //
    //        $this->setExpectedException('\Graviton\ExceptionBundle\Exception\MissingVersionFileException');
    //        $utils->getVersion(__DIR__ . '/../fixtures/invalid_composer.json');
    //    }
}
