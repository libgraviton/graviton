<?php
/**
 * compiler pass for building a map for form builder
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormMapCompilerPass implements CompilerPassInterface
{
    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    final public function process(ContainerBuilder $container)
    {
        $map = [];
        $gravitonServices = $container->findTaggedServiceIds(
            'graviton.rest'
        );
        foreach ($gravitonServices as $id => $tag) {
            $service = $container->getDefinition($id);
            $classname = $service->getClass();
            $map[$id] = $classname;
            $map[$classname] = $classname;
        }
        $container->setParameter('graviton.document.form.type.document.service_map', $map);
        //var_dump($container->getParameter('graviton.document.form.type.document.service_map'));
    }
}
