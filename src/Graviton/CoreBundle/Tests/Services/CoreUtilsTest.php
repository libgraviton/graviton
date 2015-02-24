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
     * @@dataProvider versionAndFileProvider
     */
    public function testGetVersion($version, $filePath = '')
    {
        $utils = new CoreUtils();
        $this->assertEquals($version, $utils->getVersion($filePath));
    }

    public function versionAndFileProvider()
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../../../../../composer.json'), true);

        return array(
            'get from default file' => array($composer['version']),
            'other file'            => array('0.1.0-dev', __DIR__ . '/../fixtures/valid_composer.json'),
        );
    }

    public function testGetDefaultVersion()
    {
        $utils = new CoreUtils();

        $this->setExpectedException('\RuntimeException');
        $utils->getVersion(__DIR__ . '/../fixtures/invalid_composer.json');
    }
}
