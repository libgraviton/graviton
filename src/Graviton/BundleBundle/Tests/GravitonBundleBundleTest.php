<?php
/**
 * test bundle-bundle
 */

namespace Graviton\BundleBundle\Tests;

use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\CoreBundle\GravitonCoreBundle;

/**
 * GravitonMessagingBundleTest
 *
 * @category Tests
 * @package  GravitonMessagingBundle$fieldName
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonBundleBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test getBundles method
     *
     * @return void
     */
    public function testGetBundles()
    {
        $sut = new GravitonBundleBundle();
        $expectation = array(
            new GravitonCoreBundle()
        );

        $result = $sut->getBundles();
        $this->assertInstanceOf(
            '\Graviton\BundleBundle\GravitonBundleInterface',
            $result[0]
        );
    }
}
