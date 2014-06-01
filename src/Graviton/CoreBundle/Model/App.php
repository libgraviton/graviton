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
     * get the description of the model
     *
     * @return string
     */
    public function getDescription()
    {
        return 'A graviton based app.';
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
                $description = 'Unique identifier for an app.';
                break;
            case 'title':
                $description = 'Display name for an app.';
                break;
            case 'showInMenu':
                $description = 'Define if an app should be exposed on the top level menu.';
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
        return array('id', 'title', 'showInMenu');
    }
}
