<?php
/**
 * Loads bundles from list of bundles implementing GravitonBundleInterface
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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BundleLoader
{
    /**
     * stack used during loading of bundles
     *
     * @var \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    protected $bundleStack = array();

    /**
     * final compilation of bundles
     *
     * @var \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    protected $finalBundles = array();

    /**
     * create and kickstart BundleLoader
     *
     * @param GravitonBundleBundle $bundleBundle inject kickstart bundle here
     */
    public function __construct(GravitonBundleBundle $bundleBundle)
    {
        $this->bundleStack = array($bundleBundle);
    }

    /**
     * load bundles
     *
     * @param \Symfony\Component\HttpKernel\Bundle\Bundle[] $bundles pre-loaded bundles
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function load(array $bundles)
    {
        $this->finalBundles = $bundles;

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
     * @param mixed $bundle various flavours of bundles
     *
     * @return void
     */
    private function addBundle($bundle)
    {
        if (!in_array($bundle, $this->finalBundles)) {
            if ($bundle instanceof GravitonBundleInterface) {
                $this->bundleStack = array_merge(
                    $this->bundleStack,
                    $bundle->getBundles()
                );
            }

            $this->finalBundles[] = $bundle;
        }
    }
}
