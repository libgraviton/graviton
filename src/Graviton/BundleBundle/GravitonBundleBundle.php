<?php
/**
 * bundle for autoregistering bundles in graviton
 */

namespace Graviton\BundleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\CoreBundle\GravitonCoreBundle;

/**
 * GravitonBundleBundle
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonBundleBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * This serves as kickstarter by instanciating core bundle. It has not
     * yet been decided where the remaining GravitonBundles get loaded.
     * @todo GravitonBundle loading/disco (maybe with command support).
     *
     * @return Array
     */
    public function getBundles()
    {
        return array(
            new GravitonCoreBundle(),
	    // ie.
	    // new GravitonRestBundle(),
	    // new GravitonMessagingBundle(),
	    // etc... but automated ;)
        );
    }
}
