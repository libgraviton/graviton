<?php
/**
 * a compilerpass that computes a single hash of the current generated bundles
 */

namespace Graviton\GeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GeneratorHashCompilerPass implements CompilerPassInterface
{

    /**
     * generate the hashes
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        // first type of hash: the genhash'es of all GravitonDyn bundles
        $dir = $container->getParameter('graviton.generator.dynamicbundle.dir');

        $finder = (new Finder())
            ->in($dir)
            ->files()
            ->sortByName()
            ->name('genhash');

        $dynHash = '';
        foreach ($finder as $file) {
            $dynHash .= DIRECTORY_SEPARATOR . $file->getContents();
        }

        // 2nd hash: configuration of our own graviton things (static stuff)
        $finder = (new Finder())
            ->in(__DIR__.'/../../../')
            ->path('/serializer/')
            ->path('/doctrine/')
            ->path('/schema/')
            ->notPath('/Tests/')
            ->files()
            ->sortByName()
            ->name('*.xml')
            ->name('*.json');

        $staticHash = '';
        foreach ($finder as $file) {
            $staticHash .= DIRECTORY_SEPARATOR . sha1_file($file->getPathname());
        }

        $dynHash = sha1($dynHash);
        $staticHash = sha1($staticHash);
        $allHash = sha1($dynHash . DIRECTORY_SEPARATOR . $staticHash);

        $container->setParameter('graviton.generator.hash.dyn', $dynHash);
        $container->setParameter('graviton.generator.hash.static', $staticHash);
        $container->setParameter('graviton.generator.hash.all', $allHash);
    }
}
