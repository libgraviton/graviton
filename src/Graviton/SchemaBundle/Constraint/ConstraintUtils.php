<?php
/**
 * Common functions for constraints, mostly here for performance reasons
 */

namespace Graviton\SchemaBundle\Constraint;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema;
use Graviton\RestBundle\Service\RestUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ConstraintUtils
{

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @var \stdClass
     */
    private $currentSchema;

    /**
     * @var \stdClass
     */
    private $currentData;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param DocumentManager $dm           DocumentManager
     * @param RestUtils       $restUtils    RestUtils
     * @param RequestStack    $requestStack RequestStack
     *
     */
    public function __construct(DocumentManager $dm, RestUtils $restUtils, RequestStack $requestStack)
    {
        $this->dm = $dm;
        $this->restUtils = $restUtils;
        $this->requestStack = $requestStack;
    }

    /**
     * Gets a entity from the database as a generic object. All constraints that need the saved data to compare
     * values or anything should call this function to get what they need. As this is cached in the instance,
     * it will fetched only once even if multiple constraints need that object.
     *
     * @param string $documentClass document class
     * @param string $recordId      record id
     *
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Exception
     *
     * @return object|null entity
     */
    public function getSerializedEntity($documentClass, $recordId)
    {
        if (!isset($this->entities[$documentClass][$recordId])) {
            $current = $this->dm->getRepository($documentClass)->find($recordId);

            if (is_null($current)) {
                $this->entities[$documentClass][$recordId] = null;
            } else {
                $this->entities[$documentClass][$recordId] = json_decode($this->restUtils->serializeContent($current));
            }
        }

        return $this->entities[$documentClass][$recordId];
    }

    /**
     * Returns the current request entity (as \stdClass) if possible
     *
     * @return null|object
     */
    public function getCurrentEntity()
    {
        $currentRecordId = null;

        // first, let's the one from the payload..
        if (isset($this->currentData->id)) {
            $currentRecordId = $this->currentData->id;
        }

        // if we have a request, it must override it..
        if ($this->requestStack->getCurrentRequest() instanceof Request &&
            $this->requestStack->getCurrentRequest()->attributes->has('id')
        ) {
            $currentRecordId = $this->requestStack->getCurrentRequest()->attributes->get('id');
        }

        if (isset($this->currentSchema->{'x-documentClass'}) &&
            !empty($this->currentSchema->{'x-documentClass'}) &&
            !is_null($currentRecordId)
        ) {
            return $this->getSerializedEntity($this->currentSchema->{'x-documentClass'}, $currentRecordId);
        }

        return null;
    }

    /**
     * gets the current schema. helpful for field schema validators that need access to the whole schema in some way.
     *
     * @return \stdClass
     */
    public function getCurrentSchema()
    {
        return $this->currentSchema;
    }

    /**
     * gets the current data from the client (the whole object).
     * helpful for field schema validators that need access to the whole data in some way.
     *
     * @return \stdClass
     */
    public function getCurrentData()
    {
        return $this->currentData;
    }

    /**
     * called on the first schema validation, before anything else.
     *
     * @param ConstraintEventSchema $event event
     *
     * @return void
     */
    public function onSchemaValidation(ConstraintEventSchema $event)
    {
        $this->currentSchema = $event->getSchema();
        $this->currentData = $event->getElement();
    }
}
