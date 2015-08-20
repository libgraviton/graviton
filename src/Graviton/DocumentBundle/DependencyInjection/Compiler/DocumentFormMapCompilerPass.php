<?php
/**
 * compiler pass for building a map for form builder
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
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
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * Constructor
     *
     * @param DocumentMap $documentMap Document map
     */
    public function __construct(DocumentMap $documentMap)
    {
        $this->documentMap = $documentMap;
    }

    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $map = ['stdclass' => 'stdclass'];

        $services = array_keys($container->findTaggedServiceIds('graviton.rest'));
        foreach ($services as $id) {
            $service = $container->getDefinition($id);
            $serviceClass = $service->getClass();
            $documentClass = $this->getDocumentClassFromControllerClass($serviceClass);

            $map[$id] = $documentClass;
            $map[$serviceClass] = $documentClass;
            $map[$documentClass] = $documentClass;
        }
        foreach ($this->documentMap->getDocuments() as $document) {
            $map[$document->getClass()] = $document->getClass();
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
