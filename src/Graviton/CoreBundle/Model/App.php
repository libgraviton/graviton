<?php
/**
 * basic app model
 */

namespace Graviton\CoreBundle\Model;

use Graviton\RestBundle\Model\DocumentModel;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class App extends DocumentModel
{
    /**
     * the description of the model
     *
     * @var string
     */
    protected $description = 'A graviton based app.';

    /**
     * titles for fields
     *
     * @var string[]
     */
    protected $fieldTitles = array(
        'id' => 'ID',
        'title' => 'Title',
        'showInMenu' => 'Show in Menu'
    );

    /**
     * descriptions for fields
     *
     * @var string[]
     */
    protected $fieldDescriptions = array(
        'id' => 'Unique identifier for an app.',
        'title' => 'Display name for an app.',
        'showInMenu' => 'Define if an app should be exposed on the top level menu.'
    );

    /**
     * @var string[]
     */
    protected $requiredFields = array('id', 'title', 'showInMenu');
}
