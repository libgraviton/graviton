<?php
/**
 * Generates the dynamic BundleBundle
 */

namespace Graviton\GeneratorBundle\Generator;

/**
 * Generates the dynamic BundleBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DynamicBundleBundleGenerator extends AbstractGenerator
{
    /**
     * @private string[]
     */
    protected $gravitonSkeletons;

    /**
     * Optional additions to add
     *
     * @var array
     */
    protected $additions;

    /**
     * Sets additions
     *
     * @param array $additions additions
     *
     * @return void
     */
    public function setAdditions($additions)
    {
        $this->additions = $additions;
    }

    /**
     * Generate the BundleBundle
     *
     * @param array  $bundleList            List of bundles
     * @param string $bundleBundleNamespace Namespace of our BundleBundle
     * @param string $bundleName            Name of the bundle
     * @param string $targetFilename        Where to write the list to
     *
     * @return void
     */
    public function generate(array $bundleList, $bundleBundleNamespace, $bundleName, $targetFilename)
    {
        // hm, where should that be called?
        $this->setSkeletonDirs(array('.'));

        // compose absolute classnames
        // array contains DynNamespace/NameBundle -> convert
        $absoluteList = array();
        foreach ($bundleList as $namespace) {
            $absoluteList[] = '\\' . str_replace('/', '\\', $namespace) .
                '\\' . str_replace('/', '', $namespace);
        }

        if (is_array($this->additions)) {
            $absoluteList = array_merge($absoluteList, $this->additions);
        }

        // Sort bundle names, done here to include the additional
        $bundleList = [];
        foreach ($absoluteList as $bundle) {
            $key = str_replace('\\', '', $bundle);
            $bundleList[$key] = $bundle;
        }
        ksort($bundleList);

        $parameters = array(
            'namespace' => str_replace('/', '\\', $bundleBundleNamespace),
            'bundleName' => $bundleName,
            'bundleClassList' => $bundleList
        );

        $this->renderFile('bundle/DynamicBundleBundle.php.twig', $targetFilename, $parameters);
    }
}
