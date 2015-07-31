<?php
/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\I18nBundle\Service\I18nUtils;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\Rql\Event\VisitNodeEvent;
use Xiag\Rql\Parser\AbstractNode;
use Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class I18nRqlParsingListener
{

    /**
     * @var I18nUtils
     */
    protected $intUtils;

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
     */
    public function __construct(I18nUtils $intUtils)
    {
        $this->intUtils = $intUtils;
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
     * @param mixed  $value     the values to set - array or string
     *
     * @return AbstractNode some node
     */
    private function getAlteredQueryNode($fieldName, $value)
    {
        if (is_array($value)) {
            $newNode = new OrNode();
            foreach ($value as $singleValue) {
                $newNode->addQuery(new EqNode($fieldName, $singleValue));
            }
        } else {
            $newNode = new EqNode($fieldName, $value);
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
        $class = $this->getDocumentClass();
        $isTranslatableField = false;

        if ($this->node instanceof AbstractScalarOperatorNode &&
            $class instanceof TranslatableDocumentInterface &&
            in_array($this->getDocumentFieldName(), $class->getTranslatableFields())) {
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
        unset($parts[sizeof($parts)-1]);

        return array_pop($parts);
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
        $matchingTranslations = array();

        $matchingTranslatables = $this->intUtils->findMatchingTranslatables(
            $this->node->getValue(),
            $this->getClientSearchLanguage()
        );

        foreach ($matchingTranslatables as $translatable) {
            $originalString = $translatable->getOriginal();
            if (!empty($originalString)) {
                $matchingTranslations[] = $originalString;
            }
        }

        $matches = array_unique($matchingTranslations);

        if (count($matches) === 0) {
            // if we have no matches, that means that it's either not existing OR
            // that it's not translated (= not in translatable). so we include
            // the searchval in that case to at least find original OR find nothing
            $matches = array($this->node->getValue());
        }

        return $matches;
    }

    /**
     * Returns the document class from the query
     *
     * @return DocumentModel document
     */
    private function getDocumentClass()
    {
        // find our class name
        $documentName = $this->builder->getQuery()->getClass()->getName();

        if (!class_exists($documentName)) {
            throw new \LogicException('Could not determine class name from RQL query.');
        }

        return new $documentName();
    }
}
