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
 * @link     http://swisscom.com
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
