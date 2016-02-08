<?php
/**
 * compiler pass for building a listing of fields for compiler
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Document;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\ArrayField;
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
        'boolean' => 'strictboolean',
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
                list($type, $options) = $this->resolveFieldParams(
                    $translatableFields,
                    $field->getFieldName(),
                    $field->getType()
                );

                $result[] = [
                    $field->getFormName(),
                    $type,
                    array_replace(
                        [
                            'property_path' => $field->getFieldName(),
                            'required' => $field->isRequired()
                        ],
                        $options
                    ),
                ];
            } elseif ($field instanceof ArrayField) {
                list($type, $options) = $this->resolveFieldParams(
                    $translatableFields,
                    $field->getFieldName(),
                    $field->getItemType()
                );

                $result[] = [
                    $field->getFormName(),
                    'collection',
                    [
                        'property_path' => $field->getFieldName(),
                        'type' => $type,
                        'options' => $options,
                    ],
                ];
            } elseif ($field instanceof EmbedOne) {
                $result[] = [
                    $field->getFormName(),
                    'form',
                    [
                        'property_path' => $field->getFieldName(),
                        'data_class' => $field->getDocument()->getClass(),
                        'required' => $field->isRequired(),
                    ],
                ];
            } elseif ($field instanceof EmbedMany) {
                $result[] = [
                    $field->getFormName(),
                    'collection',
                    [
                        'property_path' => $field->getFieldName(),
                        'type' => 'form',
                        'options' => ['data_class' => $field->getDocument()->getClass()],
                    ],
                ];
            }
        }
        return $result;
    }

    /**
     * Resolve simple field type
     *
     * @param array  $translatable Translatable fields
     * @param string $fieldName    Field name
     * @param string $fieldType    Field type
     * @return array Form type and options
     */
    private function resolveFieldParams(array $translatable, $fieldName, $fieldType)
    {
        if (in_array($fieldName, $translatable, true) || in_array($fieldName.'[]', $translatable, true)) {
            $type = 'translatable';
            $options = [];
        } elseif ($fieldType === 'hash') {
            $type = 'freeform';
            $options = [];
        } elseif ($fieldType === 'hasharray') {
            $type = 'collection';
            $options = ['type' => 'freeform'];
        } elseif ($fieldType === 'datearray') {
            $type = 'datearray';
            $options = [];
        } elseif (isset($this->typeMap[$fieldType])) {
            $type = $this->typeMap[$fieldType];
            $options = [];
        } else {
            $type = 'text';
            $options = [];
        }

        return [$type, $options];
    }
}
