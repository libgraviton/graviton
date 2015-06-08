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
        foreach (array_keys($gravitonServices) as $id) {
            $service = $container->getDefinition($id);
            $serviceClass = $service->getClass();
            $classname = $this->getDocumentClassFromControllerClass(
                $serviceClass
            );
            $map[$id] = $classname;
            $map[$classname] = $classname;
            $map[$serviceClass] = $classname;
        }
        $container->setParameter('graviton.document.form.type.document.service_map', $map);
    }

    /**
     * get document class from controller class
     *
     * @param string $name class name from dic
     *
     * @return string
     */
    private function getDocumentClassFromControllerClass($name)
    {
        $documentClass = str_replace('\\Controller\\', '\\Document\\', $name);
        if (substr($documentClass, -10) == 'Controller' && substr($documentClass, -11) != '\\Controller') {
            $documentClass = substr($documentClass, 0, -10);
        }
        $documentClass = str_replace('.controller.', '.document.', $documentClass);
        return $documentClass;
    }
}
