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
    protected $i18nUtils;

    /**
     * @var AbstractNode
     */
    protected $node;

    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(I18nUtils $i18nUtils)
    {
        $this->i18nUtils = $i18nUtils;
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

        if ($this->isTranslatableFieldNode()) {

        }

        return $event;
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

        if ($class instanceof TranslatableDocumentInterface &&
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
