<?php
/**
 * DocumentMap class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

use Symfony\Component\Finder\Finder;

/**
 * Document map
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     */
    public function __construct(Finder $doctrineFinder, Finder $serializerFinder)
    {
        $doctrineMap = $this->loadDoctrineClassMap($doctrineFinder);
        $serializerMap = $this->loadSerializerClassMap($serializerFinder);

        foreach ($doctrineMap as $className => $doctrineMapping) {
            $this->mappings[$className] = [
                'doctrine'   => $doctrineMap[$className],
                'serializer' => isset($serializerMap[$className]) ? $serializerMap[$className] : null,
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
            throw new \InvalidArgumentException(sprintf('No XML mapping found for document "%s"', $className));
        }

        return $this->documents[$className] = $this->processDocument(
            $className,
            $this->mappings[$className]['doctrine'],
            $this->mappings[$className]['serializer']
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
     * @param string       $className          Class name
     * @param \DOMDocument $doctrineDocument   Doctrine XML mapping
     * @param \DOMDocument $serializerDocument Serializer XML mapping
     * @return Document
     */
    private function processDocument(
        $className,
        \DOMDocument $doctrineDocument,
        \DOMDocument $serializerDocument = null
    ) {
        if ($serializerDocument === null) {
            $serializerFields = [];
        } else {
            $serializerFields = array_reduce(
                $this->getSerializerFields($serializerDocument),
                function (array $fields, array $field) {
                    $fields[$field['fieldName']] = $field;
                    return $fields;
                },
                []
            );
        }

        $fields = [];
        foreach ($this->getDoctrineFields($doctrineDocument) as $doctrineField) {
            $serializerField = isset($serializerFields[$doctrineField['name']]) ?
                $serializerFields[$doctrineField['name']] :
                null;

            $fields[] = new Field(
                $doctrineField['type'],
                $doctrineField['name'],
                $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                $serializerField === null ? false : $serializerField['readOnly']
            );
        }
        foreach ($this->getDoctrineEmbedOneFields($doctrineDocument) as $doctrineField) {
            $serializerField = isset($serializerFields[$doctrineField['name']]) ?
                $serializerFields[$doctrineField['name']] :
                null;

            $fields[] = new EmbedOne(
                $this->getDocument($doctrineField['type']),
                $doctrineField['name'],
                $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                $serializerField === null ? false : $serializerField['readOnly']
            );
        }
        foreach ($this->getDoctrineEmbedManyFields($doctrineDocument) as $doctrineField) {
            $serializerField = isset($serializerFields[$doctrineField['name']]) ?
                $serializerFields[$doctrineField['name']] :
                null;

            $fields[] = new EmbedMany(
                $this->getDocument($doctrineField['type']),
                $doctrineField['name'],
                $serializerField === null ? $doctrineField['name'] : $serializerField['exposedName'],
                $serializerField === null ? false : $serializerField['readOnly']
            );
        }

        return new Document($className, $fields);
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
            $document = new \DOMDocument();
            $document->load($file);

            $classMap[$this->getDoctrineClassName($document)] = $document;
        }

        return $classMap;
    }

    /**
     * Load doctrine class map
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

            $classMap[$this->getSerializerClassName($document)] = $document;
        }

        return $classMap;
    }

    /**
     * Get serializer class name
     *
     * @param \DOMDocument $document Serializer mapping XML document
     * @return string
     */
    private function getSerializerClassName(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);

        $node = $xpath->query('/serializer/class')->item(0);
        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException('Invalid serializer XML mapping file');
        }

        return $node->getAttribute('name');
    }

    /**
     * Get serializers
     *
     * @param \DOMDocument $document Serializer mapping XML document
     * @return array
     */
    private function getSerializerFields(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);

        return array_map(
            function (\DOMElement $element) {
                return [
                    'fieldName'   => $element->getAttribute('name'),
                    'exposedName' => $element->getAttribute('serialized-name') ?: $element->getAttribute('name'),
                    'readOnly'    => $element->getAttribute('read-only') === 'true',
                ];
            },
            iterator_to_array($xpath->query('/serializer/class[1]/property'))
        );
    }

    /**
     * Get doctrine class name
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return string
     */
    private function getDoctrineClassName(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        $node = $xpath->query('//*[self::doctrine:document or self::doctrine:embedded-document]')->item(0);
        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException('Invalid doctrine XML mapping file');
        }

        return $node->getAttribute('name');
    }

    /**
     * Get doctrine document fields
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    private function getDoctrineFields(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        return array_map(
            function (\DOMElement $element) {
                return [
                    'name' => $element->getAttribute('fieldName'),
                    'type' => $element->getAttribute('type'),
                ];
            },
            iterator_to_array($xpath->query('//doctrine:field'))
        );
    }

    /**
     * Get doctrine document embed-many fields
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    private function getDoctrineEmbedOneFields(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        return array_map(
            function (\DOMElement $element) {
                return [
                    'name' => $element->getAttribute('field'),
                    'type' => $element->getAttribute('target-document'),
                ];
            },
            iterator_to_array($xpath->query('//*[self::doctrine:embed-one or self::doctrine:reference-one]'))
        );
    }

    /**
     * Get doctrine document embed-one fields
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    private function getDoctrineEmbedManyFields(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        return array_map(
            function (\DOMElement $element) {
                return [
                    'name' => $element->getAttribute('field'),
                    'type' => $element->getAttribute('target-document'),
                ];
            },
            iterator_to_array($xpath->query('//*[self::doctrine:embed-many or self::doctrine:reference-many]'))
        );
    }
}
