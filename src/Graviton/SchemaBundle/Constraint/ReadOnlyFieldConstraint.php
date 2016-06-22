<?php
/**
 * Schema constraint that validates if readOnly: true fields are manipulated and rejects changes on those.
 */

namespace Graviton\SchemaBundle\Constraint;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema;
use Graviton\RestBundle\Service\RestUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyFieldConstraint
{

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var RestUtils
     */
    private $restUtils;

    /**
     * @var array
     */
    private $fieldMap;

    /**
     * ReadOnlyFieldConstraint constructor.
     *
     * @param DocumentManager $dm                DocumentManager
     * @param RestUtils       $restUtils         RestUtils
     * @param array           $readOnlyFieldsMap field map from compiler pass
     */
    public function __construct(DocumentManager $dm, RestUtils $restUtils, array $readOnlyFieldsMap)
    {
        $this->dm = $dm;
        $this->restUtils = $restUtils;
        $this->fieldMap = $readOnlyFieldsMap;
    }

    /**
     * Checks the readOnly fields and sets error in event if needed
     *
     * @param ConstraintEventSchema $event event class
     *
     * @return void
     */
    public function checkReadOnlyFields(ConstraintEventSchema $event)
    {
        $schema = $event->getSchema();
        $data = $event->getElement();

        if (!isset($schema->{'x-documentClass'}) || !isset($data->id)) {
            return;
        }

        $documentClass = $schema->{'x-documentClass'};

        if (!isset($this->fieldMap[$documentClass])) {
            return;
        }

        $readOnlyFields = $this->fieldMap[$documentClass];
        $recordId = $data->id;

        // get the current record
        $currentRecord = $this->getCurrentRecordSerializedAndBack($documentClass, $recordId);

        if (is_null($currentRecord)) {
            return;
        }

        // compare fields in both objects
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($readOnlyFields as $fieldName) {
            $storedValue = null;
            if ($accessor->isReadable($currentRecord, $fieldName)) {
                $storedValue = $accessor->getValue($currentRecord, $fieldName);
            }

            if (is_object($storedValue)) {
                // skip objects as a whole, we will test their readOnly properties instead
                continue;
            }

            $setValue = null;
            if ($accessor->isReadable($data, $fieldName)) {
                $setValue = $accessor->getValue($data, $fieldName);
            }

            if ($storedValue != $setValue) {
                $event->addError(
                    sprintf('The value %s is read only.', json_encode($accessor->getValue($currentRecord, $fieldName))),
                    $fieldName
                );
            }
        }
    }

    /**
     * to make sure we don't compare apple and oranges, we let the serializer
     * do what he does and bring it back as object. only then all friends like extref
     * (exposeAs) and so on are resolved and we can truly compare structures..
     *
     * @param string $documentClass document class
     * @param string $recordId      record id
     *
     * @return object stored object in presentation form
     */
    private function getCurrentRecordSerializedAndBack($documentClass, $recordId)
    {
        $current = $this->dm->getRepository($documentClass)->find($recordId);

        if (is_null($current)) {
            return null;
        }

        return json_decode($this->restUtils->serializeContent($current));
    }
}
