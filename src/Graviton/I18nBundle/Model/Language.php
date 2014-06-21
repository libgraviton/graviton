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
     * the description of the model
     *
     * @var string
     */
    protected $description = 'A Language available for i18n purposes.';

    /**
     * titles for fields
     *
     * @var string[]
     */
    protected $fieldTitles = array(
        'id' => 'Language Tag',
        'name' => 'Language',
    );

    /**
     * descriptions for fields
     *
     * @var string[]
     */
    protected $fieldDescriptions = array(
        'id' => 'A RFC2616 language tag.',
        'name' => 'Common name of a language.'
    );

    /**
     * @var string[]
     */
    protected $requiredFields = array('id', 'name');
}
