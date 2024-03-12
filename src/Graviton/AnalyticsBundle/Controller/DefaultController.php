<?php
/**
 * Entry point controller.
 */
namespace Graviton\AnalyticsBundle\Controller;

use Graviton\AnalyticsBundle\Manager\ServiceManager;
use Graviton\RestBundle\Trait\SchemaTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class DefaultController
{
    use SchemaTrait;

    /**
     * DefaultController constructor.
     *
     * @param ServiceManager $serviceManager Parsing the requested date
     */
    public function __construct(
        private ServiceManager $serviceManager
    ) {
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
        $modelName = $request->get('service');
        $model = $this->serviceManager->getAnalyticModel($modelName);

        $request->attributes->set(
            'varnishTags',
            $model->getCacheInvalidationCollections()
        );

        $resp = new JsonResponse(
            $this->serviceManager->getData($modelName)
        );

        $cacheTime = $model->getCacheTime();
        if (!empty($cacheTime) && $cacheTime > 0) {
            $resp->setCache(['max_age' => $cacheTime, 'public' => true]);
        }

        return $resp;
    }

    /**
     * renders the openapi schema
     *
     * @param Request $request request
     *
     * @return Response response
     */
    public function serviceSchemaAction(Request $request)
    {
        $name = $request->get('service');
        $format = $request->get('format');

        return $this->getResponseFromSchema(
            $this->serviceManager->getSchema($name),
            $format
        );
    }
}
