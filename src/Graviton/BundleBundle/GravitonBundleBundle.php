<?php
/**
 * Bundle for auto-registration of bundles in graviton
 */

namespace Graviton\BundleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\CoreBundle\GravitonCoreBundle;

/**
 * GravitonBundleBundle
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonBundleBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * This serves as kickstarter by instanciating core bundle. It has not
     * yet been decided where the remaining GravitonBundles get loaded.
     *
     * @todo GravitonBundle loading/disco (maybe with command support).
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        $bundles = array(
            new GravitonCoreBundle()
        );

        /*** LOOK AFTER DYNAMIC BUNDLEBUNDLE ***/

        // @todo it seems we have no container at this point - how to make this configurable?
        $dynamicBundleBundle = '\GravitonDyn\BundleBundle\GravitonDynBundleBundle';

        if (class_exists($dynamicBundleBundle)) {
            $bundles[] = new $dynamicBundleBundle();
        }

        return $bundles;
    }
}
