<?php
/** version information compiler pass */

namespace Graviton\CoreBundle\Compiler;

use Graviton\CommonBundle\Component\Deployment\VersionInformation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class VersionCompilerPass implements CompilerPassInterface
{

    /**
     * VersionCompilerPass constructor.
     *
     * @param VersionInformation $versionInformation version util
     */
    public function __construct(private VersionInformation $versionInformation)
    {
    }

    /**
     * add version information of packages to the container
     *
     * @param ContainerBuilder $container Container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $versionInformation = [
            'self' => 'unknown'
        ];

        $selfName = $container->getParameter('graviton.version.self_package_name');
        if (!empty($selfName)) {
            $versionInformation['self'] = $this->getPackageVersion($selfName);
        }

        $desiredVersions = $container->getParameter('graviton.version.desired_versions');
        if (!empty($desiredVersions) && is_array($desiredVersions)) {
            foreach ($desiredVersions as $name) {
                $versionInformation[$name] = $this->getPackageVersion($name);
            }
        }

        // for version header
        $versionHeader = '';
        foreach ($versionInformation as $name => $version) {
            $versionHeader .= $name . ': ' . $version . '; ';
        }

        $versionInformation['php'] = $this->versionInformation->getPhpVersion();

        // add stuff just for service, not header (exts)
        $extList = $container->getParameter('graviton.version.ext_list');
        if (!empty($extList) && is_array($extList)) {
            foreach ($extList as $name) {
                $version = $this->versionInformation->getPhpExtVersion($name);
                if ($version !== false) {
                    $versionInformation['ext-'.$name] = $version;
                }
            }
        }

        $container->setParameter(
            'graviton.core.version.data',
            $versionInformation
        );
        $container->setParameter(
            'graviton.core.version.header',
            trim($versionHeader)
        );
    }

    /**
     * returns the version for a package
     *
     * @param string $name package name
     *
     * @return string version string
     */
    public function getPackageVersion($name)
    {
        return $this->versionInformation->getPrettyVersion($name);
    }
}
