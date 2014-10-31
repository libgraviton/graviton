<?php

namespace Graviton\GeneratorBundle\Generator;

/**
 * Generates a BundeList - a list of Bundles in a PHP parsable structure
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
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
     * Generate the BundeList
     *
     * @param array  $bundleList     List of bundles
     * @param string $bundleBundleNamespace Namespace of our BundleBundle
     * @param string $bundleName     Name of the bundle
     * @param string $targetFilename Where to write the list to
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

        $parameters = array(
            'namespace' => str_replace('/', '\\', $bundleBundleNamespace),
            'bundleName' => $bundleName,
            'bundleClassList' => $absoluteList
        );

        $this->renderFile('bundle/DynamicBundleBundle.php.twig', $targetFilename, $parameters);
    }
}
