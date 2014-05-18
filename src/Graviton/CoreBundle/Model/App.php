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
}
