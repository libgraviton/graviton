<?php
/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\I18nBundle\Service\I18nUtils;
use Graviton\Rql\Event\VisitNodeEvent;
use Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class I18nRqlParsingListener
{

    /**
     * @var I18nUtils
     */
    protected $intUtils;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var AbstractNode
     */
    protected $node;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Constructor
     *
     * @param I18nUtils $intUtils int utils
     * @param array     $mapping  mapping
     */
    public function __construct(I18nUtils $intUtils, array $mapping)
    {
        $this->intUtils = $intUtils;
        $this->mapping = $mapping;
    }

    /**
     * @param VisitNodeEvent $event node event to visit
     *
     * @return VisitNodeEvent
     */
    public function onVisitNode(VisitNodeEvent $event)
    {
        $this->node = $event->getNode();
        $this->builder = $event->getBuilder();

        if ($this->node instanceof AbstractScalarOperatorNode && $this->isTranslatableFieldNode()) {
            $event->setNode(
                $this->getAlteredQueryNode(
                    $this->getNewNodeTargetField(),
                    $this->getAllPossibleTranslatableStrings()
                )
            );
        }

        return $event;
    }

    /**
     * Gets a new query node
     *
     * @param string $fieldName target fieldname
     * @param array  $values    the values to set
     *
     * @return AbstractNode some node
     */
    private function getAlteredQueryNode($fieldName, array $values)
    {
        $newNode = new OrNode();

        if (count($values) > 0) {
            foreach ($values as $singleValue) {
                $newNode->addQuery(new EqNode($fieldName, $singleValue));
            }
        } else {
            /**
             * if we received no valid translations (empty array), we need to
             * set some impossible condition to make sure we have an empty resultset.
             * otherwise mongo will return all records, that's not desired.
             */
            $newNode->addQuery(new EqNode(1, 2));
        }

        return $newNode;
    }

    /**
     * Returns true if the current node affects a translatable field
     *
     * @return bool true if yes, false if not
     */
    private function isTranslatableFieldNode()
    {
        $isTranslatableField = false;
        $class = $this->getDocumentClassName();

        if (isset($this->mapping[$class]) && in_array($this->getDocumentFieldName(), $this->mapping[$class])) {
            $isTranslatableField = true;
        }

        return $isTranslatableField;
    }

    /**
     * Returns the affected field name. We assume whatever depth; it's always .[lang] at the end.
     * So we strip lang and take the one before..
     * If it's only 1 (as in 'id'), this will return null.
     *
     * @return string document fieldname
     */
    private function getDocumentFieldName()
    {
        $parts = explode('.', $this->node->getField());
        array_pop($parts);
        return implode('.', $parts);
    }

    /**
     * Returns in what Language the clients search is
     *
     * @return string language
     */
    private function getClientSearchLanguage()
    {
        $parts = explode('.', $this->node->getField());
        return array_pop($parts);
    }

    /**
     * Returns the new node target field (the one without language)
     *
     * @return string new field name
     */
    private function getNewNodeTargetField()
    {
        $parts = explode('.', $this->node->getField());
        array_pop($parts);
        return implode('.', $parts);
    }

    /**
     * Looks up all matching Translatables and returns them uniquified
     *
     * @return array matching english strings
     */
    private function getAllPossibleTranslatableStrings()
    {
        $matchingTranslations = [];

        // is it a glob?
        if ($this->node->getValue() instanceof \Xiag\Rql\Parser\DataType\Glob) {
            $userValue = $this->node->getValue()->toRegex();
            $useWildcard = true;
        } else {
            $userValue = $this->node->getValue();
            $useWildcard = false;
        }

        $matchingTranslatables = $this->intUtils->findMatchingTranslatables(
            $userValue,
            $this->getClientSearchLanguage(),
            $useWildcard
        );

        foreach ($matchingTranslatables as $translatable) {
            $originalString = $translatable->getOriginal();
            if (!empty($originalString)) {
                $matchingTranslations[] = $originalString;
            }
        }

        return array_unique($matchingTranslations);
    }

    /**
     * Returns the document class from the query
     *
     * @return string class name
     */
    private function getDocumentClassName()
    {
        // find our class name
        $documentName = $this->builder->getQuery()->getClass()->getName();

        if (!class_exists($documentName)) {
            throw new \LogicException('Could not determine class name from RQL query.');
        }

        return $documentName;
    }
}
