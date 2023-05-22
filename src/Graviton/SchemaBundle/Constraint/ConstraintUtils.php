<?php
/**
 * Common functions for constraints, mostly here for performance reasons
 */

namespace Graviton\SchemaBundle\Constraint;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema;
use Graviton\RestBundle\Service\RestUtils;
use JsonSchema\Entity\JsonPointer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ConstraintUtils
{

    private DocumentManager $dm;
    private RestUtils $restUtils;

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

    private RequestStack $requestStack;

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
     * @param array  $fields        if you only need certain fields, you can specify them here
     *
     * @throws \Exception
     *
     * @return object|null entity
     */
    public function getSerializedEntity($documentClass, $recordId, array $fields = null)
    {
        if (is_array($fields)) {
            return $this->getSingleEntity($documentClass, $recordId, $fields);
        }

        if (!isset($this->entities[$documentClass][$recordId])) {
            $current = $this->getSingleEntity($documentClass, $recordId, $fields);

            if (is_null($current)) {
                $this->entities[$documentClass][$recordId] = null;
            } else {
                $this->entities[$documentClass][$recordId] = $current;
            }
        }

        return $this->entities[$documentClass][$recordId];
    }

    /**
     * returns an already serialized entity depending on fields, making sure the
     * query is fresh
     *
     * @param string $documentClass document class
     * @param string $recordId      record id
     * @param array  $fields        if you only need certain fields, you can specify them here
     *
     * @throws \Exception
     *
     * @return object|null entity
     */
    private function getSingleEntity($documentClass, $recordId, array $fields = null)
    {
        // only get certain fields! will not be cached in instance
        $queryBuilder = $this->dm->createQueryBuilder($documentClass);
        $queryBuilder->field('id')->equals($recordId);
        if (is_array($fields)) {
            $queryBuilder->select($fields);
        }
        $query = $queryBuilder->getQuery();
        $query->setRefresh(true);
        $records = array_values($query->execute()->toArray());

        if (is_array($records) && !empty($records)) {
            return json_decode($this->restUtils->serializeContent($records[0]));
        }

        return null;
    }

    /**
     * Returns the current request entity (as \stdClass) if possible
     *
     * @param array $fields if you only need certain fields, you can specify them here
     *
     * @return null|object
     */
    public function getCurrentEntity(array $fields = null)
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
            return $this->getSerializedEntity($this->currentSchema->{'x-documentClass'}, $currentRecordId, $fields);
        }

        return null;
    }

    /**
     * Returns the request method of the current request
     *
     * @return null|string the request method
     */
    public function getCurrentRequestMethod()
    {
        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            return $this->requestStack->getCurrentRequest()->getMethod();
        }
        return null;
    }

    /**
     * Returns the current request content
     *
     * @return bool|null|resource|string the content
     */
    public function getCurrentRequestContent()
    {
        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            return $this->requestStack->getCurrentRequest()->getContent();
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
     * own function to get standard path from a JsonPointer object
     *
     * @param JsonPointer|null $pointer pointer
     *
     * @return string path as string
     */
    public function getNormalizedPathFromPointer(JsonPointer $pointer = null)
    {
        $result = array_map(
            function ($path) {
                return sprintf(is_numeric($path) ? '[%d]' : '.%s', $path);
            },
            $pointer->getPropertyPaths()
        );
        return trim(implode('', $result), '.');
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
