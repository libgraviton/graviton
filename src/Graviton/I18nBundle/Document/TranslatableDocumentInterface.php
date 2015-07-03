<?php
/**
 * A translatable document
 */

namespace Graviton\I18nBundle\Document;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface TranslatableDocumentInterface
{
    /**
     * return all translatable fields
     *
     * @return string[]
     */
    public function getTranslatableFields();

    /**
     * return all pretranslated fields
     *
     * @return string[]
     */
    public function getPreTranslatedFields();
}
