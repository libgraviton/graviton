<?php

namespace Graviton\I18nBundle\Document;

/**
 * A translatable document
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class TranslatableDocument
{
    /**
     * return all translatable fields
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array();
    }
}
