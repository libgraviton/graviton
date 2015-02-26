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
     * @param string $version  Version string to be used
     * @param string $filePath Location of the file containing the version information.
     *
     * @return void
     */
    public function testGetVersion($version, $filePath = '')
    {
        $utils = new CoreUtils();
        $this->assertEquals($version, $utils->getVersion($filePath));
    }

    /**
     * Provides test sets for the getVersion() test.
     *
     * @return array
     */
    public function versionAndFileProvider()
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../../../../../composer.json'), true);

        return array(
            'get from default file' => array($composer['version']),
            'other file'            => array('0.1.0-dev', __DIR__ . '/../fixtures/valid_composer.json'),
        );
    }

    /**
     * Verifies the correct behavior of getVersion()
     *
     * @return void
     */
    public function testGetDefaultVersion()
    {
        $utils = new CoreUtils();

        $this->setExpectedException('\RuntimeException');
        $utils->getVersion(__DIR__ . '/../fixtures/invalid_composer.json');
    }
}
