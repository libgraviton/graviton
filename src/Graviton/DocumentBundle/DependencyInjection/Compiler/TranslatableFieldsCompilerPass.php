<?php
/**
 * build a list of all services that have translatable mappings
 *
 * this can be used by whoever needs to know where translatables are..
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Document;
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
class TranslatableFieldsCompilerPass implements CompilerPassInterface
{
    /**
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * Make translatable fields map and set it to parameter
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->documentMap = $container->get('graviton.document.map');
        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $map[$document->getClass()] = $this->getTranslatableFields($document);
        }
        $container->setParameter('graviton.document.type.translatable.fields', $map);
    }

    /**
     * Get document fields
     *
     * @param Document $document Document
     * @param string   $prefix   Field prefix
     * @return array
     */
    private function getTranslatableFields(Document $document, $prefix = '')
    {
        $result = [];
        foreach ($document->getFields() as $field) {
            if ($field instanceof Field) {
                if ($field->getType() == 'translatable') {
                    $result[] = $prefix.$field->getExposedName();
                }
            } elseif ($field instanceof EmbedOne) {
                $result = array_merge(
                    $result,
                    $this->getTranslatableFields(
                        $field->getDocument(),
                        $prefix.$field->getExposedName().'.'
                    )
                );
            } elseif ($field instanceof EmbedMany) {
                $result = array_merge(
                    $result,
                    $this->getTranslatableFields(
                        $field->getDocument(),
                        $prefix.$field->getExposedName().'.0.'
                    )
                );
            }
        }

        return $result;
    }
}
