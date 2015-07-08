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
class DocumentFormMapCompilerPass implements CompilerPassInterface, LoadFieldsInterface
{
    /**
     * @see \Graviton\DocumentBundle\DependencyInjection\Compiler\LoadFieldsTrait
     */
    use LoadFieldsTrait;

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
            list($doc, $bundle) = $this->getInfoFromTag($tag, $doc, $bundle);
            $this->loadFields($map, $ns, $bundle, $doc);
        }
        if (!isset($map['stdclass'])) {
            $map['stdclass'] = 'stdclass';
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

    /**
     * @param array     $map      map to add entries to
     * @param \DOMXPath $xpath    xpath access to doctrine config dom
     * @param string    $ns       namespace
     * @param string    $bundle   bundle name
     * @param string    $doc      document name
     * @param boolean   $embedded is this an embedded doc, further args are only for embeddeds
     * @param string    $name     name prefix of document the embedded field belongs to
     * @param string    $prefix   prefix to add to embedded field name
     *
     * @return void
     */
    public function loadFieldsFromDOM(
        array &$map,
        \DOMXPath $xpath,
        $ns,
        $bundle,
        $doc,
        $embedded,
        $name = '',
        $prefix = ''
    ) {
        foreach (['//doctrine:embed-one', '//doctrine:embed-many'] as $basePath) {
            $embedNodes = $xpath->query($basePath);
            foreach ($embedNodes as $node) {
                $fieldName = $node->getAttribute('field');
                $targetDocument = $node->getAttribute('target-document');

                $this->loadEmbeddedDocuments(
                    $map,
                    $xpath->query(sprintf("%s[@field='%s']", $basePath, $fieldName)),
                    $targetDocument,
                    $basePath == '//doctrine:embed-many'
                );
                $map[$targetDocument] = $targetDocument;
            }
        }
    }
}
