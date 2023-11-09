<?php
/**
 * Entry point controller.
 */
namespace Graviton\AnalyticsBundle\Controller;

use Graviton\AnalyticsBundle\Manager\ServiceManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DefaultController
{
    /** @var ServiceManager */
    private $serviceManager;

    /**
     * DefaultController constructor.
     * @param ServiceManager $serviceManager Parsing the requested date
     */
    public function __construct(
        ServiceManager $serviceManager
    ) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * prints the data
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        return new JsonResponse(
            $this->serviceManager->getServices()
        );
    }

    /**
     * @return Response
     */
    public function optionsAction()
    {
        $resp = new Response();
        $resp->setStatusCode(Response::HTTP_NO_CONTENT);
        return $resp;
    }

    /**
     * executes the analytics
     *
     * @param Request $request request
     *
     * @return JsonResponse
     */
    public function serviceAction(Request $request)
    {
        $request->attributes->set(
            'varnishTags',
            $this->serviceManager->getCurrentAnalyticModel()->getCacheInvalidationCollections()
        );

        $resp = new JsonResponse(
            $this->serviceManager->getData()
        );

        $cacheTime = $this->serviceManager->getCurrentAnalyticModel()->getCacheTime();
        if (!empty($cacheTime) && $cacheTime > 0) {
            $resp->setCache(['max_age' => $cacheTime, 'public' => true]);
        }

        return $resp;
    }

    /**
     * @return JsonResponse
     */
    public function serviceSchemaAction()
    {
        return new JsonResponse(
            $this->serviceManager->getSchema()
        );
    }
}
