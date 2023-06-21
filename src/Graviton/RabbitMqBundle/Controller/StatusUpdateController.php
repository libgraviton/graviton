<?php
/**
 * controller to update event status with 1 request
 */

namespace Graviton\RabbitMqBundle\Controller;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\RestUtils;
use GravitonDyn\EventStatusBundle\Document\EventStatus;
use GravitonDyn\EventStatusBundle\Document\EventStatusInformationEmbedded;
use GravitonDyn\EventStatusBundle\Document\EventStatusStatus;
use GravitonDyn\EventStatusBundle\Document\EventStatusStatusActionEmbedded;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class StatusUpdateController
{

    private ?DocumentModel $model;
    private RestUtils $restUtils;

    /**
     * @param DocumentModel|null $model     model
     * @param RestUtils          $restUtils restutils
     */
    public function __construct(?DocumentModel $model, RestUtils $restUtils)
    {
        $this->model = $model;
        $this->restUtils = $restUtils;
    }

    /**
     * updates the state of an eventstatus
     *
     * @param Request $request request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function updateStatusAction(Request $request)
    {
        if (in_array($request->getMethod(), ['GET', 'OPTIONS'])) {
            return new JsonResponse(
                [
                    'message' => 'you can update an event status directly using this route using PUT /event/status/' .
                        '{eventId}/{workerId}/{status}/{actionId?} (last param actionId is optional). ' .
                        'in the body you can include a new "information" entry if necessary.'
                ]
            );
        }

        $eventId = $request->attributes->get('eventId');
        $workerId = $request->attributes->get('workerId');
        $status = $request->attributes->get('status');
        $actionId = $request->attributes->get('actionId');

        /**
         * @var $existingRecord EventStatus
         */
        $existingRecord = $this->model->find($eventId);

        /**
         * find the right status
         */
        $isUpdated = false;
        foreach ($existingRecord->getStatus() as $statusEntry) {
            if ($statusEntry->getWorkerid() == $workerId) {
                $statusEntry->setStatus($status);
                if ($actionId != null) {
                    $statusEntry->setAction($this->getAction($actionId));
                }
                $isUpdated = true;
            }
        }

        if (!$isUpdated) {
            // add new, is not in array yet
            $statusEntry = new EventStatusStatus();
            $statusEntry->setStatus($status);
            $statusEntry->setWorkerid($workerId);
            if ($actionId != null) {
                $statusEntry->setAction($this->getAction($actionId));
            }
            $existingRecord->setStatus(
                array_merge($existingRecord->getStatus(), [$statusEntry])
            );
        }

        // information entry?
        $json = \json_decode($request->getContent(), true);
        if (!empty($json) && is_array($json) && count($json) > 0) {
            /**
             * @var $informationItem EventStatusInformationEmbedded
             */
            $informationItem = $this->restUtils->deserializeContent(
                $request->getContent(),
                EventStatusInformationEmbedded::class
            );

            if (!empty($informationItem->getContent()) &&
                !empty($informationItem->getWorkerid()) &&
                !empty($informationItem->getType())
            ) {
                $existingRecord->setInformation(
                    array_merge(
                        is_array($existingRecord->getInformation()) ? $existingRecord->getInformation() : [],
                        [$informationItem]
                    )
                );
            }
        }

        $this->model->updateRecord($eventId, $existingRecord);

        return new Response();
    }


    /**
     * gets an action
     *
     * @param string $name name
     *
     * @return EventStatusStatusActionEmbedded action
     */
    private function getAction($name)
    {
        $action = new EventStatusStatusActionEmbedded();
        $action->setRef(ExtReference::create('EventStatusAction', $name));
        return $action;
    }
}
