<?php
/**
 * compiler pass for building a listing of fields for compiler
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Document;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\EmbedMany;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\EmbedOne;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Field;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormFieldsCompilerPass implements CompilerPassInterface
{
    /**
     * @var DocumentMap
     */
    private $documentMap;
    /**
     * @var array
     */
    private $typeMap = [
        'string'  => 'text',
        'extref'  => 'extref',
        'int'     => 'integer',
        'float'   => 'number',
        'boolean' => 'checkbox',
        'date'    => 'datetime',
    ];

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
        $map = ['stdclass' => []];
        foreach ($this->documentMap->getDocuments() as $document) {
            $map[$document->getClass()] = $this->getFormFields($document);
        }
        $container->setParameter('graviton.document.form.type.document.field_map', $map);
    }

    /**
     * Get document fields
     *
     * @param Document $document Document
     * @return array
     */
    private function getFormFields(Document $document)
    {
        $reflection = new \ReflectionClass($document->getClass());
        if ($reflection->implementsInterface('Graviton\I18nBundle\Document\TranslatableDocumentInterface')) {
            $instance = $reflection->newInstanceWithoutConstructor();
            $translatableFields = $instance->getTranslatableFields();
        } else {
            $translatableFields = [];
        }

        $result = [];
        foreach ($document->getFields() as $field) {
            if ($field instanceof Field) {
                if (in_array($field->getFieldName(), $translatableFields, true)) {
                    $type = 'translatable';
                } elseif ($field->getType() === 'hash') {
                    $type = 'freeform';
                } elseif (isset($this->typeMap[$field->getType()])) {
                    $type = $this->typeMap[$field->getType()];
                } else {
                    $type = 'text';
                }

                $result[] = [
                    $field->getFieldName(),
                    $field->getExposedName(),
                    $type,
                    [],
                ];
            } elseif ($field instanceof EmbedOne) {
                $result[] = [
                    $field->getFieldName(),
                    $field->getExposedName(),
                    'form',
                    ['data_class' => $field->getDocument()->getClass()],
                ];
            } elseif ($field instanceof EmbedMany) {
                $result[] = [
                    $field->getFieldName(),
                    $field->getExposedName(),
                    'collection',
                    [
                        'type' => 'form',
                        'options' => ['data_class' => $field->getDocument()->getClass()],
                    ],
                ];
            }
        }
        return $result;
    }
}
