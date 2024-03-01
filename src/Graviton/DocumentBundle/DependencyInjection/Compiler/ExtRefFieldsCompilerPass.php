<?php
/**
 * build a list of all services that have extref mappings
 *
 * This list later gets used during rendering URLs in the output where we
 * need to know when and wht really needs rendering after our doctrine
 * custom type is only able to spit out the raw data during hydration.
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\ArrayField;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Document;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\EmbedMany;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\EmbedOne;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Field;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtRefFieldsCompilerPass implements CompilerPassInterface
{

    /**
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * Make extref fields map and set it to parameter
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->documentMap = $container->get('graviton.document.map');

        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $map[$document->getClass()] = $this->processDocument($document);
        }

        $container->setParameter('graviton.document.extref.fields', $map);
    }

    /**
     * Recursive doctrine document processing
     *
     * @param Document $document      Document
     * @param string   $exposedPrefix Exposed field prefix
     * @return array
     */
    private function processDocument(Document $document, $exposedPrefix = '')
    {
        $result = [];
        foreach ($document->getFields() as $field) {
            if ($field instanceof Field) {
                if ($field->getType() === 'extref') {
                    $result[] = $exposedPrefix.$field->getExposedName();
                }
            } elseif ($field instanceof ArrayField) {
                if ($field->getItemType() === 'extref') {
                    $result[] = $exposedPrefix.$field->getExposedName().'.0';
                }
            } elseif ($field instanceof EmbedOne) {
                $result = array_merge(
                    $result,
                    $this->processDocument(
                        $field->getDocument(),
                        $exposedPrefix.$field->getExposedName().'.'
                    )
                );
            } elseif ($field instanceof EmbedMany) {
                $result = array_merge(
                    $result,
                    $this->processDocument(
                        $field->getDocument(),
                        $exposedPrefix.$field->getExposedName().'.0.'
                    )
                );
            }
        }

        return $result;
    }
}
