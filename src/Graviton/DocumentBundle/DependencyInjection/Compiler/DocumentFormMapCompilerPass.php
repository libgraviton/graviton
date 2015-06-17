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
            $serviceClass = $service->getClass();
            $classname = $this->getDocumentClassFromControllerClass(
                $serviceClass
            );
            $map[$id] = $classname;
            $map[$classname] = $classname;
            $map[$serviceClass] = $classname;

            list($ns, $bundle,, $doc) = explode('.', $id);
            if (empty($bundle) || empty($doc)) {
                continue;
            }
            if ($bundle == 'core' && $doc == 'main') {
                continue;
            }
            if (!empty($tag[0]['collection'])) {
                $doc = $tag[0]['collection'];
                $bundle = $tag[0]['collection'];
            }
            $this->loadFields($map, $ns, $bundle, $doc);
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

    use LoadFieldsTrait;

    /**
     * @param array        $map      map to add entries to
     * @param \DOMDOcument $dom      doctrine config dom
     * @param \DOMXPath    $xpath    xpath access to doctrine config dom
     * @param string       $ns       namespace
     * @param string       $bundle   bundle name
     * @param string       $doc      document name
     * @param boolean      $embedded is this an embedded doc, further args are only for embeddeds
     * @param string       $name     name prefix of document the embedded field belongs to
     * @param string       $prefix   prefix to add to embedded field name
     *
     * @return void
     */
    protected function loadFieldsFromDOM(
        array &$map,
        \DOMDocument $dom,
        \DOMXPath $xpath,
        $ns,
        $bundle,
        $doc,
        $embedded,
        $name = '',
        $prefix = ''
    ) {
        $embedNodes = $xpath->query("//doctrine:embed-one");
        foreach ($embedNodes as $node) {
            $fieldName = $node->getAttribute('field');
            $targetDocument = $node->getAttribute('target-document');

            $this->loadEmbeddedDocuments(
                $map,
                $xpath->query("//doctrine:embed-one[@field='".$fieldName."']"),
                $targetDocument
            );
            $map[$targetDocument] = $targetDocument;
        }
        $embedNodes = $xpath->query("//doctrine:embed-many");
        foreach ($embedNodes as $node) {
            $fieldName = $node->getAttribute('field');
            $targetDocument = $node->getAttribute('target-document');

            $this->loadEmbeddedDocuments(
                $map,
                $xpath->query("//doctrine:embed-many[@field='".$fieldName."']"),
                $targetDocument,
                true
            );
            $map[$targetDocument] = $targetDocument;
        }
    }
}
