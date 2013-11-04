<?php
/**
 * load bundles from bundles implementing GravitonBundleInterface
 */

namespace Graviton\BundleBundle\Loader;

use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * BundleLoader
 *
 * This class loads additional bundles if a module implements 
 * GravitonBundleInterface.
 * It does not check for circularity so we need to take care of only loading
 * GravitonBundles through this in the rare exception case.
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class BundleLoader
{
    /**
     * stack used during loading of bundles
     *
     * @var Array
     */
    protected $bundleStack = array();

    /**
     * final compilation of bundles
     *
     * @var Array
     */
    protected $finalBundles = array();

    /**
     * create and kickstart BundleLoader
     *
     * @param GravitonBundleBundle $bundleBundle inject kickstart bundle here
     *
     * @return void
     */
    public function __construct(GravitonBundleBundle $bundleBundle)
    {
        $this->bundleStack = array($bundleBundle);
    }

    /**
     * load bundles from kickstarter bundle
     *
     * @return Array
     */
    public function load()
    {
        while (!empty($this->bundleStack)) {
            $bundle = array_shift($this->bundleStack);
            $this->addBundle($bundle);
        }
        return $this->finalBundles;
    }

    /**
     * add bundles
     *
     * adds all bundles to finalBundles.
     * adds the results of getBundles to bundleStack if GravitonBundleInterface
     * was implemented.
     *
     * @param Mixed $bundle various flavours of bundles
     *
     * @return void
     */
    private function addBundle($bundle)
    {
        if ($bundle instanceof GravitonBundleInterface) {
            $this->bundleStack = array_merge(
                $this->bundleStack,
                $bundle->getBundles()
            );
        }
        $this->finalBundles[] = $bundle;
    }
}
