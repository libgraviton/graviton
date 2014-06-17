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
            case 'tag':
                $title = 'Language tag';
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
            case 'tag':
                $description = 'An rfc2616 language tag.';
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
        return array('tag');
    }
}
