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
class Translatable extends DocumentModel
{
    /**
     * the description of the model
     *
     * @var string
     */
    protected $description = 'A Translatable string available for i18n purposes.';

    /**
     * titles for fields
     *
     * @var string[]
     */
    protected $fieldTitles = array(
        'id' => 'ID',
        'domain' => 'Domain',
        'locale' => 'Locale',
        'original' => 'Source String',
        'translated' => 'Translated String',
    );

    /**
     * descriptions for fields
     *
     * @var string[]
     */
    protected $fieldDescriptions = array(
        'id' => 'Internal identifier of a translation string.',
        'domain' => 'Domain a given string is applicable to.',
        'locale' => 'Language Locale',
        'original' => 'Original version of string (usually english).',
        'translated' => 'Translated string',
    );

    /**
     * @var string[]
     */
    protected $requiredFields = array('domain', 'locale', 'original');
}
