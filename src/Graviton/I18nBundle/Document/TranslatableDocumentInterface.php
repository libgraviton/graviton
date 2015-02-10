<?php
/**
 * A translatable document
 *
 * PHP Version 2
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\I18nBundle\Document;

/**
 * A translatable document
 *
 * @category I18nBundle
 * @package  Graviton
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
}
