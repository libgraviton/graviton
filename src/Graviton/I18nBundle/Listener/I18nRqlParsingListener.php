<?php
/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\I18nBundle\Service\I18nUtils;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\RqlParser\AbstractNode;
use Graviton\RqlParser\Glob;
use Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode;
use Graviton\RqlParser\Node\Query\LogicalOperator\OrNode;
use Graviton\RqlParser\Node\Query\ScalarOperator\EqNode;

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
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    private $createdNodes = [];

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
        if ($this->isOurNode($event->getNode())) {
            return $event;
        }

        $this->node = $event->getNode();
        $this->builder = $event->getBuilder();
        $this->className = $event->getClassName();

        if ($this->node instanceof AbstractScalarOperatorNode && $this->isTranslatableFieldNode()) {
            $alteredNode = $this->getAlteredQueryNode(
                $this->getDocumentFieldName(),
                $this->getAllPossibleTranslatableStrings()
            );

            if (!empty($alteredNode->getQueries())) {
                $event->setNode($alteredNode);
            }
        }

        return $event;
    }

    /**
     * Gets a new query node
     *
     * @param string $fieldName target fieldname
     * @param array  $values    the values to set
     *
     * @return OrNode some node
     */
    private function getAlteredQueryNode($fieldName, array $values)
    {
        $newNode = new OrNode();
        $defaultLanguageFieldName = $fieldName.'.'.$this->intUtils->getDefaultLanguage();

        foreach ($values as $singleValue) {
            $newNode->addQuery($this->getEqNode($fieldName, $singleValue));
            // search default language field (as new structure only has 'en' set after creation and no save)
            $newNode->addQuery(
                $this->getEqNode(
                    $defaultLanguageFieldName,
                    $singleValue
                )
            );
        }

        // add default match
        $newNode->addQuery($this->getEqNode($this->node->getField(), $this->getNodeValue()));

        if (!$this->nodeFieldNameHasLanguage()) {
            // if no language, we add it to the query to default node for default language
            $newNode->addQuery(
                $this->getEqNode(
                    $defaultLanguageFieldName,
                    $this->getNodeValue()
                )
            );
        }

        return $newNode;
    }

    /**
     * gets the node value
     *
     * @throws \MongoException
     *
     * @return \MongoRegex|string value
     */
    private function getNodeValue()
    {
        if ($this->node->getValue() instanceof Glob) {
            return new \MongoRegex('/'.$this->node->getValue()->toRegex().'/');
        }

        return $this->node->getValue();
    }

    /**
     * Returns true if the current node affects a translatable field
     *
     * @return bool true if yes, false if not
     */
    private function isTranslatableFieldNode()
    {
        $isTranslatableField = false;
        if (isset($this->mapping[$this->className]) &&
            in_array($this->getDocumentFieldName(), $this->mapping[$this->className])
        ) {
            $isTranslatableField = true;
        }

        return $isTranslatableField;
    }

    /**
     * get EqNode instance and remember that we created it..
     *
     * @param string $fieldName  field name
     * @param string $fieldValue field value
     *
     * @return EqNode node
     */
    private function getEqNode($fieldName, $fieldValue)
    {
        $node = new EqNode($fieldName, $fieldValue);
        $this->createdNodes[] = $node;
        return $node;
    }

    /**
     * check if we created the node previously
     *
     * @param EqNode $node node
     *
     * @return bool true if yes, false otherwise
     */
    private function isOurNode($node)
    {
        foreach ($this->createdNodes as $createdNode) {
            if ($createdNode == $node) {
                return true;
            }
        }
        return false;
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
        if ($this->nodeFieldNameHasLanguage()) {
            array_pop($parts);
        }
        return implode('.', $parts);
    }

    /**
     * if the node field name targets a language or not
     *
     * @return bool true if yes, false otherwise
     */
    private function nodeFieldNameHasLanguage()
    {
        $parts = explode('.', $this->node->getField());
        if (!empty($parts)) {
            $lastPart = $parts[count($parts) - 1];
            // only remove when language
            if (in_array($lastPart, $this->intUtils->getLanguages())) {
                return true;
            }
        }
        return false;
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
     * Looks up all matching Translatables and returns them uniquified
     *
     * @return array matching english strings
     */
    private function getAllPossibleTranslatableStrings()
    {
        $matchingTranslations = [];

        // is it a glob?
        if ($this->node->getValue() instanceof Glob) {
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
}
