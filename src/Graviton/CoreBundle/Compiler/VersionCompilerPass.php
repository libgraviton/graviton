<?php
/** A custom compiler pass class */

namespace Graviton\CoreBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionCompilerPass implements CompilerPassInterface
{

    /**
     * add version numbers of packages to the container
     *
     * @param ContainerBuilder $container Container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter(
            'graviton.core.version.data',
            $this->getPackageVersions($container->getParameter('kernel.root_dir'))
        );
    }

    /**
     * @param string $rootDir path to root dir
     *
     * @return array version numbers of packages
     */
    private function getPackageVersions($rootDir)
    {
        // -i installed packages
        $packageNames = shell_exec('composer show -i');
        $packages = explode(PHP_EOL, $packageNames);
        //last index is always empty
        array_pop($packages);

        $versions = array();
        foreach ($packages as $package) {
            preg_match_all('/([^\s]+)/', $package, $match);
            if (strpos($match[0][0], 'grv') === 0 | $match[0][0] === 'graviton') {
                array_push($versions, array('id' => $match[0][0], 'version' => $match[0][1] ));
            }
        }
        $composerFile = !empty($composerFile) ? $composerFile : $rootDir . '/../composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            if (JSON_ERROR_NONE === json_last_error() && !empty($composer['version'])) {
                array_push($versions, array('id' => 'graviton', 'version' => $composer['version'] ));
            }
        }

        return $versions;
    }
}
