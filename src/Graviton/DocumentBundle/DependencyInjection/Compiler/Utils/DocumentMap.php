<?php
/**
 * DocumentMap class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Document map
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentMap
{
    /**
     * @var array
     */
    private $mappings = [];
    /**
     * @var Document[]
     */
    private $documents = [];

    /**
     * Constructor
     *
     * @param Finder $doctrineFinder   Doctrine mapping finder
     * @param Finder $serializerFinder Serializer mapping finder
     * @param Finder $schemaFinder     Schema finder
     */
    public function __construct(
        Finder $doctrineFinder,
        Finder $serializerFinder,
        Finder $schemaFinder
    ) {
        $doctrineMap = $this->loadDoctrineClassMap($doctrineFinder);
        $serializerMap = $this->loadSerializerClassMap($serializerFinder);
        $schemaMap = $this->loadSchemaClassMap($schemaFinder);

        foreach ($doctrineMap as $className => $doctrineMapping) {
            $this->mappings[$className] = [
                'doctrine' => $doctrineMap[$className],
                'serializer' => isset($serializerMap[$className]) ? $serializerMap[$className] : null,
                'schema' => isset($schemaMap[$className]) ? $schemaMap[$className] : null,
            ];
        }
    }

    /**
     * Get document
     *
     * @param string $className Document class
     * @return Document
     */
    public function getDocument($className)
    {
        if (isset($this->documents[$className])) {
            return $this->documents[$className];
        }
        if (!isset($this->mappings[$className])) {
            throw new \InvalidArgumentException(sprintf('No mapping found for document "%s"', $className));
        }

        return $this->documents[$className] = $this->processDocument(
            $className,
            $this->mappings[$className]['doctrine'],
            $this->mappings[$className]['serializer'],
            $this->mappings[$className]['schema']
        );
    }

    /**
     * Get all documents
     *
     * @return Document[]
     */
    public function getDocuments()
    {
        return array_map([$this, 'getDocument'], array_keys($this->mappings));
    }

    /**
     * Process document
     *
     * @param string      $className         Class name
     * @param array       $doctrineMapping   Doctrine mapping
     * @param \DOMElement $serializerMapping Serializer XML mapping
     * @param array       $schemaMapping     Schema mapping
     *
     * @return Document
     */
    private function processDocument(
        $className,
        array $doctrineMapping,
        \DOMElement $serializerMapping = null,
        array $schemaMapping = null
    ) {
        if ($serializerMapping === null) {
            $serializerFields = [];
        } else {
            $serializerFields = array_reduce(
                $this->getSerializerFields($serializerMapping),
                function (array $fields, array $field) {
                    $fields[$field['fieldName']] = $field;
                    return $fields;
                },
                []
            );
        }

        if ($schemaMapping === null) {
            $schemaFields = [];
        } else {
            $schemaFields = $schemaMapping;
        }

        $fields = [];
        foreach ($this->getDoctrineFields($doctrineMapping) as $doctrineField) {
            $serializerField = isset($serializerFields[$doctrineField['name']]) ?
                $serializerFields[$doctrineField['name']] :
                null;
            $schemaField = isset($schemaFields[$doctrineField['name']]) ?
                $schemaFields[$doctrineField['name']] :
                null;

            if ($doctrineField['type'] === 'collection') {
                $fields[] = new ArrayField(
                    $serializerField === null ? 'array<string>' : $serializerField['fieldType'],
                    $doctrineField['name'],
                    $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                    !isset($schemaField['readOnly']) ? false : $schemaField['readOnly'],
                    ($schemaField === null || !isset($schemaField['required'])) ? false : $schemaField['required'],
                    !isset($schemaField['recordOriginException']) ? false : $schemaField['recordOriginException'],
                    !isset($schemaField['restrictions']) ? [] : $schemaField['restrictions']
                );
            } else {
                $fields[] = new Field(
                    $doctrineField['type'],
                    $doctrineField['name'],
                    $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                    !isset($schemaField['readOnly']) ? false : $schemaField['readOnly'],
                    ($schemaField === null || !isset($schemaField['required'])) ? false : $schemaField['required'],
                    $serializerField === null ? false : $serializerField['searchable'],
                    !isset($schemaField['recordOriginException']) ? false : $schemaField['recordOriginException'],
                    !isset($schemaField['restrictions']) ? [] : $schemaField['restrictions']
                );
            }
        }
        foreach ($this->getDoctrineEmbedOneFields($doctrineMapping) as $doctrineField) {
            $serializerField = isset($serializerFields[$doctrineField['name']]) ?
                $serializerFields[$doctrineField['name']] :
                null;
            $schemaField = isset($schemaFields[$doctrineField['name']]) ?
                $schemaFields[$doctrineField['name']] :
                null;

            $fields[] = new EmbedOne(
                $this->getDocument($doctrineField['type']),
                $doctrineField['name'],
                $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                !isset($schemaField['readOnly']) ? false : $schemaField['readOnly'],
                ($schemaField === null || !isset($schemaField['required'])) ? false : $schemaField['required'],
                !isset($schemaField['recordOriginException']) ? false : $schemaField['recordOriginException'],
                !isset($schemaField['restrictions']) ? [] : $schemaField['restrictions']
            );
        }
        foreach ($this->getDoctrineEmbedManyFields($doctrineMapping) as $doctrineField) {
            $serializerField = isset($serializerFields[$doctrineField['name']]) ?
                $serializerFields[$doctrineField['name']] :
                null;
            $schemaField = isset($schemaFields[$doctrineField['name']]) ?
                $schemaFields[$doctrineField['name']] :
                null;

            $fields[] = new EmbedMany(
                $this->getDocument($doctrineField['type']),
                $doctrineField['name'],
                $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                !isset($schemaField['readOnly']) ? false : $schemaField['readOnly'],
                ($schemaField === null || !isset($schemaField['required'])) ? false : $schemaField['required'],
                !isset($schemaField['recordOriginException']) ? false : $schemaField['recordOriginException'],
                !isset($schemaField['restrictions']) ? [] : $schemaField['restrictions']
            );
        }

        $doc = new Document($className, $fields);

        // stuff that belongs to the whole document
        if (isset($schemaMapping['_base']['solr']['fields'])) {
            $doc->setSolrFields($schemaMapping['_base']['solr']['fields']);
        }
        if (isset($schemaMapping['_base']['solr']['aggregate'])) {
            $doc->setSolrAggregate($schemaMapping['_base']['solr']['aggregate']);
        }

        return $doc;
    }

    /**
     * Load doctrine class map
     *
     * @param Finder $finder Mapping finder
     * @return array
     */
    private function loadDoctrineClassMap(Finder $finder)
    {


        $classMap = [];
        foreach ($finder as $file) {
            $classMap = array_merge(
                $classMap,
                Yaml::parseFile($file)
            );
        }

        // filter out superclasses
        $classMap = array_filter(
            $classMap,
            function ($classEntry) {
                return (!isset($classEntry['type']) || $classEntry['type'] != 'mappedSuperclass');
            }
        );

        var_dump($classMap); die;

        return $classMap;
    }

    /**
     * Load serializer class map
     *
     * @param Finder $finder Mapping finder
     * @return array
     */
    private function loadSerializerClassMap(Finder $finder)
    {
        $classMap = [];
        foreach ($finder as $file) {
            $document = new \DOMDocument();
            $document->load($file);

            $xpath = new \DOMXPath($document);

            $classMap = array_reduce(
                iterator_to_array($xpath->query('//class')),
                function (array $classMap, \DOMElement $element) {
                    $classMap[$element->getAttribute('name')] = $element;
                    return $classMap;
                },
                $classMap
            );
        }

        return $classMap;
    }

    /**
     * Load schema class map
     *
     * @param Finder $finder Mapping finder
     * @return array
     */
    private function loadSchemaClassMap(Finder $finder)
    {
        $classMap = [];
        foreach ($finder as $file) {
            $schema = json_decode(file_get_contents($file), true);

            if (!isset($schema['x-documentClass'])) {
                continue;
            }

            foreach ($schema['required'] as $field) {
                $classMap[$schema['x-documentClass']][$field]['required'] = true;
            }
            foreach ($schema['searchable'] as $field) {
                $classMap[$schema['x-documentClass']][$field]['searchable'] = 1;
            }
            foreach ($schema['readOnlyFields'] as $field) {
                $classMap[$schema['x-documentClass']][$field]['readOnly'] = true;
            }

            // flags from fields
            if (is_array($schema['properties'])) {
                foreach ($schema['properties'] as $fieldName => $field) {
                    if (isset($field['recordOriginException']) && $field['recordOriginException'] == true) {
                        $classMap[$schema['x-documentClass']][$fieldName]['recordOriginException'] = true;
                    }
                    if (isset($field['x-restrictions'])) {
                        $classMap[$schema['x-documentClass']][$fieldName]['restrictions'] = $field['x-restrictions'];
                    } else {
                        $classMap[$schema['x-documentClass']][$fieldName]['restrictions'] = [];
                    }
                }
            }

            if (isset($schema['solr']) && is_array($schema['solr']) && !empty($schema['solr'])) {
                $classMap[$schema['x-documentClass']]['_base']['solr'] = $schema['solr'];
            } else {
                $classMap[$schema['x-documentClass']]['_base']['solr'] = [];
            }
        }

        return $classMap;
    }

    /**
     * Get serializer fields
     *
     * @param \DOMElement $mapping Serializer XML mapping
     * @return array
     */
    private function getSerializerFields(\DOMElement $mapping)
    {
        $xpath = new \DOMXPath($mapping->ownerDocument);

        return array_map(
            function (\DOMElement $element) {
                return [
                    'fieldName'   => $element->getAttribute('name'),
                    'fieldType'   => $this->getSerializerFieldType($element),
                    'exposedName' => $element->getAttribute('serialized-name') ?: $element->getAttribute('name'),
                    'readOnly'    => $element->getAttribute('read-only') === 'true',
                    'searchable'  => (int) $element->getAttribute('searchable')
                ];
            },
            iterator_to_array($xpath->query('property', $mapping))
        );
    }

    /**
     * Get serializer field type
     *
     * @param \DOMElement $field Field node
     * @return string|null
     */
    private function getSerializerFieldType(\DOMElement $field)
    {
        if ($field->getAttribute('type')) {
            return $field->getAttribute('type');
        }

        $xpath = new \DOMXPath($field->ownerDocument);

        $type = $xpath->query('type', $field)->item(0);
        return $type === null ? null : $type->nodeValue;
    }

    /**
     * Get doctrine document fields
     *
     * @param array $mapping Doctrine mapping
     * @return array
     */
    private function getDoctrineFields(array $mapping)
    {
        if (!isset($mapping['fields'])) {
            return [];
        }

        return array_map(
            function ($key, $value) {
                if (!isset($value['type'])) {
                    $value['type'] = '';
                }

                return [
                    'name' => $key,
                    'type' => $value['type']
                ];
            },
            array_keys($mapping['fields']),
            $mapping['fields']
        );
    }

    /**
     * Get doctrine document embed-one fields
     *
     * @param array $mapping Doctrine mapping
     * @return array
     */
    private function getDoctrineEmbedOneFields(array $mapping)
    {
        return $this->getRelationList($mapping, 'One');
    }

    /**
     * Get doctrine document embed-many fields
     *
     * @param array $mapping Doctrine mapping
     * @return array
     */
    private function getDoctrineEmbedManyFields(array $mapping)
    {
        return $this->getRelationList($mapping, 'Many');
    }

    /**
     * gets list of relations
     *
     * @param array  $mapping mapping
     * @param string $suffix  suffix
     *
     * @return array relations
     */
    private function getRelationList($mapping, $suffix)
    {
        if (!isset($mapping['embed'.$suffix]) && !isset($mapping['reference'.$suffix])) {
            return [];
        }

        $relations = [];
        if (isset($mapping['embed'.$suffix])) {
            $relations = array_merge($relations, $mapping['embed'.$suffix]);
        }
        if (isset($mapping['reference'.$suffix])) {
            $relations = array_merge($relations, $mapping['reference'.$suffix]);
        }

        return array_map(
            function ($key, $value) {
                return [
                    'name' => $key,
                    'type' => $value['targetDocument']
                ];
            },
            array_keys($relations),
            $relations
        );
    }

    /**
     * Gets an array of all fields, flat with full internal name in dot notation as key and
     * the exposed field name as value. You can pass a callable to limit the fields return a subset of fields.
     * If the callback returns true, the field will be included in the output. You will get the field definition
     * passed to your callback.
     *
     * @param Document $document        The document
     * @param string   $documentPrefix  Document field prefix
     * @param string   $exposedPrefix   Exposed field prefix
     * @param callable $callback        An optional callback where you can influence the number of fields returned
     * @param boolean  $returnFullField if true, the function returns the full field object instead of the full path
     *
     * @return array
     */
    public function getFieldNamesFlat(
        Document $document,
        $documentPrefix = '',
        $exposedPrefix = '',
        callable $callback = null,
        $returnFullField = false
    ) {
        $result = [];
        foreach ($document->getFields() as $field) {
            if ($this->getFlatFieldCheckCallback($field, $callback)) {
                if ($returnFullField) {
                    $setValue = $field;
                } else {
                    $setValue = $exposedPrefix . $field->getExposedName();
                }
                $result[$documentPrefix . $field->getFieldName()] = $setValue;
            }

            if ($field instanceof ArrayField) {
                if ($this->getFlatFieldCheckCallback($field, $callback)) {
                    if ($returnFullField) {
                        $setValue = $field;
                    } else {
                        $setValue = $exposedPrefix . $field->getExposedName() . '.0';
                    }
                    $result[$documentPrefix . $field->getFieldName() . '.0'] = $setValue;
                }
            } elseif ($field instanceof EmbedOne) {
                $result = array_merge(
                    $result,
                    $this->getFieldNamesFlat(
                        $field->getDocument(),
                        $documentPrefix.$field->getFieldName().'.',
                        $exposedPrefix.$field->getExposedName().'.',
                        $callback,
                        $returnFullField
                    )
                );
            } elseif ($field instanceof EmbedMany) {
                if ($this->getFlatFieldCheckCallback($field, $callback)) {
                    if ($returnFullField) {
                        $setValue = $field;
                    } else {
                        $setValue = $exposedPrefix . $field->getExposedName() . '.0';
                    }
                    $result[$documentPrefix . $field->getFieldName() . '.0'] = $setValue;
                }
                $result = array_merge(
                    $result,
                    $this->getFieldNamesFlat(
                        $field->getDocument(),
                        $documentPrefix.$field->getFieldName().'.0.',
                        $exposedPrefix.$field->getExposedName().'.0.',
                        $callback,
                        $returnFullField
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Simple function to check whether a given shall be returned in the output of getFieldNamesFlat
     * and the optional given callback there.
     *
     * @param AbstractField $field    field
     * @param callable|null $callback optional callback
     *
     * @return bool|mixed true if field should be returned, false otherwise
     */
    private function getFlatFieldCheckCallback($field, callable $callback = null)
    {
        if (!is_callable($callback)) {
            return true;
        }

        return call_user_func($callback, $field);
    }
}
