<?php
/**
 * Entry point controller.
 */
namespace Graviton\AnalyticsBundle\Controller;

use Graviton\AnalyticsBundle\Manager\ServiceManager;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * @return JsonResponse
     */
    public function indexAction()
    {
        $data = $this->serviceManager->getServices();

        return new JsonResponse($data);
    }

    /**
     * @return JsonResponse
     */
    public function serviceAction()
    {
        $data = $this->serviceManager->getData();

        return new JsonResponse($data);
    }

    /**
     * @return JsonResponse
     */
    public function serviceSchemaAction()
    {
        $data = $this->serviceManager->getSchema();

        return new JsonResponse($data);
    }
}
