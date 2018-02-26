<?php
/**
 * Schema constraint that validates if readOnly: true fields are manipulated and rejects changes on those.
 */

namespace Graviton\SchemaBundle\Constraint;

use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VersionServiceConstraint
{

    /** DB Field name used for validation and incremental */
    const FIELD_NAME = 'version';

    /** Header name used to inform user */
    const HEADER_NAME = 'X-Current-Version';

    /** @var int */
    private $version;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * ReadOnlyFieldConstraint constructor.
     *
     * @param ConstraintUtils $utils utils
     */
    public function __construct(ConstraintUtils $utils)
    {
        $this->utils = $utils;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Checks the readOnly fields and sets error in event if needed
     *
     * @param ConstraintEventSchema $event event class
     *
     * @return void
     */
    public function checkVersionField(ConstraintEventSchema $event)
    {
        if (!$this->isVersioningService()) {
            return;
        }

        $data = $event->getElement();

        // get the current record
        if ($currentRecord = $this->utils->getCurrentEntity()) {
            $userVersion = $this->getUserVersion($data);
            $storedVersion = $this->getVersionFromObject($currentRecord);
            if ($userVersion !== $storedVersion) {
                $event->addError(
                    sprintf(
                        'The value you provided does not match current version of the document. '.
                        'See the \'%s\' header in this response to determine current version.',
                        self::HEADER_NAME
                    ),
                    self::FIELD_NAME
                );

                // store version for response header
                $this->version = $storedVersion;
            }
        }
    }

    /**
     * tells whether the current service has versioning activated or not
     *
     * @return bool true if yes, false otherwise
     */
    public function isVersioningService()
    {
        $schema = $this->utils->getCurrentSchema();
        if (isset($schema->{'x-versioning'}) && $schema->{'x-versioning'} === true) {
            return true;
        }
        return false;
    }

    /**
     * returns the version from a given object
     *
     * @param object $object object
     *
     * @return int|null null or the specified version
     */
    private function getVersionFromObject($object)
    {
        $version = null;

        if ($this->accessor->isReadable($object, self::FIELD_NAME)) {
            $version = $this->accessor->getValue($object, self::FIELD_NAME);
        }
        return $version;
    }

    /**
     * Gets the user provided version, handling different scenarios
     *
     * @param object $object object
     *
     * @return int|null null or the specified version
     */
    private function getUserVersion($object)
    {
        if ($this->utils->getCurrentRequestMethod() == 'PATCH') {
            $content = json_decode($this->utils->getCurrentRequestContent(), true);

            $hasVersion = array_filter(
                $content,
                function ($val) {
                    if ($val['path'] == '/'.self::FIELD_NAME) {
                        return true;
                    }
                    return false;
                }
            );

            if (empty($hasVersion)) {
                return -1;
            }
        }

        return $this->getVersionFromObject($object);
    }

    /**
     * Setting if needed the headers to let user know what was the new version.
     *
     * @param FilterResponseEvent $event SF response event
     * @return void
     */
    public function setCurrentVersionHeader(FilterResponseEvent $event)
    {
        if ($this->version) {
            $event->getResponse()->headers->set(self::HEADER_NAME, $this->version);
        }
    }
}
