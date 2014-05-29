<?php

/**
 * basic app model
 */

namespace Graviton\TaxonomyBundle\Model;

use Graviton\RestBundle\Model\DocumentModel;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @category GravitonTaxonomyBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class Country extends DocumentModel
{
    /**
     * get the description of the model
     *
     * @return String
     */
    public function getDescription()
    {
        return 'A country record.';
    }

    /**
     * get description of a given field
     *
     * @param String $field field name
     *
     * @return String
     */
    public function getDescriptionOfField($field)
    {
        $description = '';
        switch ($field) {
            case 'id':
                $description = 'ISO 3166-1 alpha-3 code.';
                break;
            case 'name':
                $description = 'Country name.';
                break;
            case 'isoCode':
                $description = 'ISO 3166-1 alpha-2 code (aka cTLD).';
                break;
            case 'capitalCity':
                $description = 'Capital city.';
                break;
            case 'latitude':
                $description = 'N/S geographic coordinate.';
                break;
            case 'longitude':
                $description = 'W/O geographic coordinate.';
                break;
        }

        return $description;
    }

    /**
     * get required fields for this model
     *
     * @return Array
     */
    public function getRequiredFields()
    {
        return array('id', 'name', 'isoCode');
    }
}
