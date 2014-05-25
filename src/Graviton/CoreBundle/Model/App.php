<?php
/**
 * basic app model
 */

namespace Graviton\CoreBundle\Model;

use Graviton\RestBundle\Model\Doctrine\ODM as Model;
use Graviton\CoreBundle\Repository\AppRepository;

/**
 * Model based on Graviton\RestBundle\Model\Doctrine\ODM.
 *
 * For now this gets an apprepository through constructor
 * injection. This needs to be changed properly according
 * to what we end up doing with the rest of Graviton\RestBundle\Model.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class App extends Model
{
    /**
     * create new app model
     *
     * @param AppRepository $apps Repository for constructor injection
     *
     * @return void
     */
    public function __construct(AppRepository $apps)
    {
        $this->repository = $apps;
    }

    /**
     * get repository instance
     *
     * @return AppRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * get the description of the model
     *
     * @return String
     */
    public function getDescription()
    {
        return 'A graviton based app.';
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
     * @return Array
     */
    public function getRequiredFields()
    {
        return array('id', 'title', 'showInMenu');
    }
}
