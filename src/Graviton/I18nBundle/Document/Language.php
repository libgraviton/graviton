<?php

namespace Graviton\I18nBundle\Document;

/**
 * Graviton\I18nBundle\Document\Language
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class Language extends TranslatableDocument
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
