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
        $utils = new CoreUtils($filePath);
        $this->assertEquals($version, $utils->getVersionById('graviton')->version);
    }

    /**
     * Provides test sets for the getVersion() test.
     *
     * @return array
     */
    public function versionAndFileProvider()
    {
        return array(
            'get from default file' => array('0.25.1', __DIR__ . '/../../../../../app/cache/test/'),
        );
    }

    /**
     * Verifies the correct behavior of getVersion()
     *
     * @return void
     */
    public function testGetDefaultVersion()
    {
        $utils = new CoreUtils('notARealPath');

        $this->setExpectedException('\Graviton\ExceptionBundle\Exception\MissingVersionFileException');
        $utils->getVersion(__DIR__ . '/../fixtures/invalid_composer.json');
    }
}
