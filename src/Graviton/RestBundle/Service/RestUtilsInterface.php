<?php
/**
 * initerface for ReestUtils helper class
 */

namespace Graviton\RestBundle\Service;

use Graviton\RestBundle\Model\DocumentModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use JMS\Serializer\Serializer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface RestUtilsInterface
{
    /**
     * Builds a map of baseroutes (controllers) to its relevant route to the actions.
     * ignores schema stuff.
     *
     * @return array grouped array of basenames and actions..
     */
    public function getServiceRoutingMap();

    /**
     * Public function to serialize stuff according to the serializer rules.
     *
     * @param object $content Any content to serialize
     * @param string $format  Which format to serialize into
     *
     * @throws \Exception
     *
     * @return string $content Json content
     */
    public function serializeContent($content, $format = 'json');

    /**
     * Deserialize the given content throw an exception if something went wrong
     *
     * @param string $content       Request content
     * @param string $documentClass Document class
     * @param string $format        Which format to deserialize from
     *
     * @throws \Exception
     *
     * @return object|array|integer|double|string|boolean
     */
    public function deserializeContent($content, $documentClass, $format = 'json');

    /**
     * Get the serializer
     *
     * @return Serializer
     */
    public function getSerializer();

    /**
     * It has been deemed that we search for OPTION routes in order to detect our
     * service routes and then derive the rest from them.
     *
     * @return array An array with option routes
     */
    public function getOptionRoutes();

    /**
     * Based on $baseName, this function returns all routes that match this basename..
     * So if you pass graviton.cont.action; it will return all route names that start with the same.
     * In our routing naming schema, this means all the routes from the same controller.
     *
     * @param string $baseName basename
     *
     * @return array array with matching routes
     */
    public function getRoutesByBasename($baseName);

    /**
     * validate raw json input
     *
     * @param Request       $request  request
     * @param Response      $response response
     * @param DocumentModel $model    model
     * @param string        $content  Alternative request content.
     *
     * @return void
     */
    public function checkJsonRequest(Request $request, Response $response, DocumentModel $model, $content = '');
}
