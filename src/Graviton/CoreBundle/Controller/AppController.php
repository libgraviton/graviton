<?php
/**
 * controller for app entities
 */

namespace Graviton\CoreBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\ODM\MongoDB\DocumentManager;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
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
    /**
     * create new controller
     *
     * @param AppRepository   $apps       app repo
     * @param DocumentManager $dm         document manager
     * @param Serializer      $serializer serializer
     *
     * @return void
     */
    public function __construct(
        AppRepository $apps,
        DocumentManager $dm,
        Serializer $serializer
    ) {
        $this->apps = $apps;
        $this->dm = $dm;
        $this->serializer = $serializer;
    }

    /**
     * return all the records
     *
     * @return Response
     */
    public function allAction()
    {
        $apps = $this->apps->findAll();
        $apps = array_values($apps);
        $response = new Response(
            $this->serializer->serialize($apps, 'json'),
            200,
            array('content-type' => 'application/json')
        );
        return $response;
    }
}
