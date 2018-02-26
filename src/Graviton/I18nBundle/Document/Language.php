<?php
/**
 * Graviton\I18nBundle\Document\Language
 */

namespace Graviton\I18nBundle\Document;

/**
 * Graviton\I18nBundle\Document\Language
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Language implements TranslatableDocumentInterface
{
    /**
     * construct language document
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array('name');
    }

    /**
     * return pretranslated fields
     *
     * @return string[]
     */
    public function getPreTranslatedFields()
    {
        return array();
    }

    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id language tag value
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Name
     *
     * @param string $name name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
