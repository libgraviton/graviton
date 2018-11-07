<?php
/**
 * bundle class
 */

namespace somenamespace;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * Graviton dynamic BundleBundle - DO NOT MANIPULATE!
 *
 * @category GravitonDynBundleBundle
 * @author   tester <test@test.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonDynBundleBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * set up graviton symfony dynamic bundles
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array(
            /* START BUNDLE LIST */
            new DudeBundle(),
            new FranzBundle(),
            new Dude2Bundle(),
            new KaiserFranzBundle()
            /* END BUNDLE LIST */
        );
    }
}
