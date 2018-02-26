<?php
/**
 * DocumentFieldNamesCompilerPass class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Document;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentFieldNamesCompilerPass implements CompilerPassInterface
{
    /**
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->documentMap = $container->get('graviton.document.map');

        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $map[$document->getClass()] = $this->getFieldNames($document);
        }
        $container->setParameter('graviton.document.field.names', $map);
    }

    /**
     * Get field names
     *
     * @param Document $document Document
     * @return array
     */
    private function getFieldNames(Document $document)
    {
        $result = [];
        foreach ($document->getFields() as $field) {
            $result[$field->getFieldName()] = $field->getExposedName();
        }
        return $result;
    }
}
