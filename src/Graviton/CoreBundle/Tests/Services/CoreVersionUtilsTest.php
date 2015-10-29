<?php
/**
 * Basic functional test for CoreVersionUtils class
 */

namespace Graviton\CoreBundle\Tests\Service;

use Graviton\CoreBundle\Service\CoreVersionUtils;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CoreVersionUtilsTest extends RestTestCase
{
    /**l
     * @var CoreVersionUtils
     */
    private $coreVersionUtils;

    /**
     * @dataProvider provideVersionNumberForCheck
     *
     * @param string $versionToCheck  String which should be checked
     * @param string $expectedVersion Expected Version string
     * @return void
     */
    public function testCheckVersionNumber($versionToCheck, $expectedVersion)
    {
        $this->coreVersionUtils = $this->getMockBuilder('Graviton\CoreBundle\Service\CoreVersionUtils')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $returnedVersion = $this->coreVersionUtils->checkVersionNumber($versionToCheck);
        $this->assertEquals($expectedVersion, $returnedVersion);
    }

    /**
     * Data provider for testCheckVersionNumber
     *
     * @return array
     */
    public function provideVersionNumberForCheck()
    {
        return array(
            array('1.2.3.4', 'v1.2.3'),
            array('1.2.3', '1.2.3'),
            array('1.2','v1.2.0'),
            array('v1.2.3', 'v1.2.3'),
            array('v1.2.3-alpha1', 'v1.2.3'),
            array('dev-master', 'dev-master'),
            array('dev-feature/test_branch', 'dev-feature/test_branch'),
            array('dev-9d0b8cf7c7a607684e978a2777ebdd36e348ba75', 'dev-9d0b8cf7c7a607684e978a2777ebdd36e348ba75')
        );
    }
}
