<?php
/**
 * twig extension
 */

namespace Graviton\GeneratorBundle\Twig;

use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GeneratorExtension implements ExtensionInterface
{

    /**
     * @var array map from our type to the doctrine annotation type
     */
    private $doctrineOwnFieldMapping = [
        'hash[]' => 'hasharray',
        'date[]' => 'datearray',
        'translatable[]' => 'translatablearray'
    ];

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return TokenParserInterface[]
     */
    public function getTokenParsers()
    {
        return [];
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors()
    {
        return [];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return TwigTest[]
     */
    public function getTests()
    {
        return [];
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array<array> First array of unary operators, second array of binary operators
     */
    public function getOperators()
    {
        return [];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('doctrineFieldAnnotation', [$this, 'getDoctrineFieldAnnotation']),
            new TwigFunction('doctrineIndexesAnnotation', [$this, 'getDoctrineIndexesAnnotation'])
        ];
    }

    /**
     * generates the valid doctrine annotation for a field
     *
     * @param array $field field information
     *
     * @return string annotation
     */
    public function getDoctrineFieldAnnotation($field)
    {
        if (strpos($field['type'], 'Graviton') !== false) {
            $addedProperties = '';

            // object type
            if (isset($field['relType']) && $field['relType'] == 'ref') {
                $refType = 'Reference';
                $addedProperties .= ', cascade={"all"}, orphanRemoval=false';
            } else {
                $refType = 'Embed';
            }

            $refAmount = 'One';
            if (substr($field['type'], -2) == '[]') {
                $refAmount = 'Many';
                $addedProperties .= ', strategy="setArray"';
            }

            // clean [] if present
            $className = str_replace('[]', '', $field['type']);
            if ($refType == 'Embed') {
                $className .= 'Embedded';
            }

            return sprintf(
                '@ODM\%s(targetDocument="%s"%s)',
                $refType.$refAmount,
                $className,
                $addedProperties
            );
        }

        $fieldType = $field['type'];
        if (isset($this->doctrineOwnFieldMapping[$fieldType])) {
            $fieldType = $this->doctrineOwnFieldMapping[$fieldType];
        }

        if (substr($fieldType, -2) == '[]') {
            $fieldType = 'collection';
        }

        return sprintf(
            '@ODM\Field(type="%s")',
            $fieldType
        );
    }

    /**
     * generates the valid doctrine annotation for the document indexes
     *
     * @param string   $collectionName collection name
     * @param array    $indexes        index fields
     * @param string[] $ensureIndexes  indexes to ensure
     * @param string[] $textIndexes    text indexes
     *
     * @return string
     */
    public function getDoctrineIndexesAnnotation($collectionName, $indexes, $ensureIndexes = null, $textIndexes = null)
    {
        if (!is_array($indexes)) {
            $indexes = [];
        }
        if (!is_array($ensureIndexes)) {
            $ensureIndexes = [];
        }

        $indexes = array_map(
            [$this, 'getSingleDoctrineIndexAnnotation'],
            array_merge($indexes, $ensureIndexes)
        );

        // text index?
        if (!empty($textIndexes)) {
            $indexes[] = $this->getDoctrineTextIndexAnnotation($collectionName, $textIndexes);
        }
        return '@ODM\Indexes({'.implode(', ', $indexes).'})';
    }

    /**
     * generates a single index to be used within Indexes
     *
     * @param string $index field name
     *
     * @return string index annotation
     */
    private function getSingleDoctrineIndexAnnotation($index)
    {
        return sprintf(
            ' @ODM\Index(keys={"%s"="asc"}, name="%s", background=true)',
            $index,
            $index
        );
    }

    /**
     * generates the annotation for a text index
     *
     * @param string   $collectionName collection name
     * @param string[] $textIndexes    indexes
     *
     * @return string annotation
     */
    private function getDoctrineTextIndexAnnotation($collectionName, $textIndexes)
    {
        $options = [
            'default_language' => 'de',
            'language_override' => 'none',
            'weights' => $textIndexes
        ];

        $keys = array_map(
            function ($fieldName) {
                return sprintf(
                    '"%s"="text"',
                    $fieldName
                );
            },
            array_keys($textIndexes)
        );

        return sprintf(
            ' @ODM\Index(keys={%s}, name="%s", background=true, options=%s)',
            implode(', ', $keys),
            $collectionName.'Text',
            json_encode($options)
        );
    }
}
