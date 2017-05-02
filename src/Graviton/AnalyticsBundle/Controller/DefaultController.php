<?php
/**
 * Entry point controller.
 */
namespace Graviton\AnalyticsBundle\Controller;

use Graviton\AnalyticsBundle\Request\ParamConverter\ServiceConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefaultController extends Controller
{
    /**
     * @param ServiceConverter $manager Api Service to Find the Needed response data
     * @param Request          $request Sf Request data
     * @ParamConverter("manager", converter="graviton.analytics.request_service_converter")
     * @return JsonResponse
     */
    public function serviceAction(ServiceConverter $manager, Request $request)
    {
        $data = $manager->getData();

        return new JsonResponse($data);
    }

    /**
     * @param ServiceConverter $manager Api Service to Find the Needed response data
     * @param Request          $request Sf Request data
     * @ParamConverter("manager", converter="graviton.analytics.request_service_converter")
     * @return JsonResponse
     */
    public function serviceSchemaAction(ServiceConverter $manager, Request $request)
    {
        $data = $manager->getSchema();

        return new JsonResponse($data);
    }
}
