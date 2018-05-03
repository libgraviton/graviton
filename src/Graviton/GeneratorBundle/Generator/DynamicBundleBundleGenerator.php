<?php
/**
 * Generates the dynamic BundleBundle
 */

namespace Graviton\GeneratorBundle\Generator;

/**
 * Generates the dynamic BundleBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DynamicBundleBundleGenerator extends AbstractGenerator
{

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
        $absoluteList = [];
        foreach ($bundleList as $namespace) {
            $absoluteList[] = '\\' . str_replace('/', '\\', $namespace) .
                '\\' . str_replace('/', '', $namespace);
        }

        if (is_array($this->additions)) {
            $absoluteList = array_merge($absoluteList, $this->additions);
        }

        $absoluteList = array_unique($absoluteList);
        sort($absoluteList);

        $parameters = array(
            'namespace' => str_replace('/', '\\', $bundleBundleNamespace),
            'bundleName' => $bundleName,
            'bundleClassList' => $absoluteList
        );

        $this->renderFile('bundle/DynamicBundleBundle.php.twig', $targetFilename, $parameters);
    }
}
