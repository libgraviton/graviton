<?php
/**
 * test bundle-bundle
 */

namespace Graviton\BundleBundle\Tests;

use Graviton\BundleBundle\GravitonBundleBundle;
use PHPUnit\Framework\TestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonBundleBundleTest extends TestCase
{
    /**
     * test getBundles method
     *
     * @return void
     */
    public function testGetBundles()
    {
        $sut = new GravitonBundleBundle();

        $result = $sut->getBundles();
        $this->assertInstanceOf(
            '\Graviton\BundleBundle\GravitonBundleInterface',
            $result[0]
        );
    }
}
