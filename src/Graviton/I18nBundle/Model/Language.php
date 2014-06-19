<?php

namespace Graviton\I18nBundle\Model;

use Graviton\RestBundle\Model\DocumentModel;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class Language extends DocumentModel
{
    /**
     * get the description of the model
     *
     * @return string
     */
    public function getDescription()
    {
        return 'A Language available for i18n purposes.';
    }

    /**
     * get the title of a given field
     *
     * @param string $field field name
     *
     * @return string
     */
    public function getTitleOfField($field)
    {
        $title = '';
        switch ($field) {
            case 'id':
                $title = 'Language Tag';
                break;
            case 'name':
                $title = 'Language';
                break;
        }

        return $title;
    }

    /**
     * get description of a given field
     *
     * @param string $field field name
     *
     * @return string
     */
    public function getDescriptionOfField($field)
    {
        $description = '';
        switch ($field) {
            case 'id':
                $description = 'A RFC2616 language tag.';
                break;
            case 'name':
                $description = 'Common name of a language.';
                break;
        }

        return $description;
    }

    /**
     * get required fields for this object
     *
     * @return string[]
     */
    public function getRequiredFields()
    {
        return array('id', 'name');
    }
}
