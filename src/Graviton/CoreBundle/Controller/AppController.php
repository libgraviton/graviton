<?php
/**
 * controller for app entities
 */

namespace Graviton\CoreBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Graviton\CoreBundle\Repository\AppRepository;

/**
 * AppController
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AppController extends FOSRestController
{
    public function __construct(AppRepository $apps)
    {
        $this->apps = $apps;
    }

    public function allAction()
    {
        return $this->apps->findAll();
    }
}
