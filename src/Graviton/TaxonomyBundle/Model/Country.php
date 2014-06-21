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
     * the description of the model
     *
     * @var string
     */
    protected $description = 'A country record.';

    /**
     * titles for fields
     *
     * @var string[]
     */
    protected $fieldTitles = array(
        'id' => 'ID',
        'name' => 'Name',
        'isoCode' => 'ISO Code',
        'capitalCity' => 'Capital',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude'
    );

    /**
     * descriptions for fields
     *
     * @var string[]
     */
    protected $fieldDescriptions = array(
        'id' => 'ISO 3166-1 alpha-3 code.',
        'name' => 'Country name.',
        'isoCode' => 'ISO 3166-1 alpha-2 code (aka cTLD).',
        'capitalCity' => 'Capital city.',
        'latitude' => 'N/S geographic coordinate.',
        'longitude' => 'W/O geographic coordinate.'
    );

    /**
     * @var string[]
     */
    protected $requiredFields = array('id', 'name', 'isoCode');
}
