<?php
/**
 * Document for representing Products.
 */

namespace Graviton\CoreBundle\Document;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * Product
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Product implements TranslatableDocumentInterface
{
    /**
     * @var string app id
     */
    protected $id;

    /**
     * @var string[] app title in multiple languages
     */
    protected $name;

    /**
     * make title translatable
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array();
    }

    /**
     * return pretranslated fields
     *
     * @return string[]
     */
    public function getPreTranslatedFields()
    {
        return array('name');
    }

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
     * Get name
     *
     * @return string[] $name
     */
    public function getName()
    {
        return $this->name;
    }
}
