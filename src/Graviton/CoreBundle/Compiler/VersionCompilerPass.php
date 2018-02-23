<?php
/** A custom compiler pass class */

namespace Graviton\CoreBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
        $versions = array('self'=>'unknown');
        $pathVersions = $container->getParameter('kernel.root_dir') . '/../versions.yml';
        if (file_exists($pathVersions)) {
            $yaml = new Parser();
            $versions = $yaml->parse(file_get_contents($pathVersions));
        }
        $container->setParameter(
            'graviton.core.version.data',
            $versions
        );
    }
}
