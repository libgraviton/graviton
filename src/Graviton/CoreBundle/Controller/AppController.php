<?php
/**
 * controller for app entities
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\ODM\MongoDB\DocumentManager;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Graviton\CoreBundle\Repository\AppRepository;
use Graviton\CoreBundle\Model\App;

/**
 * AppController
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AppController extends RestController
{
}
