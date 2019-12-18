<?php
/** version information compiler pass */

namespace Graviton\CoreBundle\Compiler;

use Jean85\PrettyVersions;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VersionCompilerPass implements CompilerPassInterface
{

    /**
     * @var PrettyVersions
     */
    private $prettyVersions;

    /**
     * VersionCompilerPass constructor.
     *
     * @param PrettyVersions $prettyVersions version util
     */
    public function __construct(PrettyVersions $prettyVersions)
    {
        $this->prettyVersions = $prettyVersions;
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
        $rootDir = $container->getParameter('kernel.root_dir');

        if (strpos($rootDir, 'vendor') !== false) {
            $configurationFile = $rootDir.'/../../../../app';
        } else {
            $configurationFile = $rootDir;
        }

        $configurationFile .= '/config/version_service.yml';

        if (!file_exists($configurationFile)) {
            throw new \LogicException(
                'Could not read version configuration file "'.$configurationFile.'"'
            );
        }

        $config = Yaml::parseFile($configurationFile);
        $versionInformation = [
            'self' => 'unknown'
        ];

        if (isset($config['selfName'])) {
            $versionInformation['self'] = $this->getPackageVersion($config['selfName']);
        }


        if (isset($config['desiredVersions']) && is_array($config['desiredVersions'])) {
            foreach ($config['desiredVersions'] as $name) {
                $versionInformation[$name] = $this->getPackageVersion($name);
            }
        }

        // for version header
        $versionHeader = '';
        foreach ($versionInformation as $name => $version) {
            $versionHeader .= $name . ': ' . $version . '; ';
        }

        $versionInformation['php'] = PHP_VERSION;

        // add stuff just for service, not header (exts)
        if (isset($config['ext']) && is_array($config['ext'])) {
            foreach ($config['ext'] as $name) {
                $version = phpversion($name);
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
        return (string) $this->prettyVersions::getVersion($name);
    }
}
