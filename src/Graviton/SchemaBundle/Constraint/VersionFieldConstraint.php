<?php
/**
 * Field constraint that validates if the posted version is the same as the one in DB.
 */

namespace Graviton\SchemaBundle\Constraint;

use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventFormat;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionFieldConstraint
{
    /** DB Field name used for validation and incremental */
    const FIELD_NAME = 'version';

    /** Header name used to inform user */
    const HEADER_NAME = 'X-Current-Version';

    /** @var ConstraintUtils  */
    private $utils;

    /** @var Integer */
    private $version;

    /**
     * Constructor
     *
     * @param ConstraintUtils $utils Utils
     */
    public function __construct(ConstraintUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Check if the version is correct: versioning
     * -> Post, even if posted it should be version 1
     * -> Put, should be the same value. Reject if not.
     * -> Patch, no check, only increment
     *
     * Default, if object have version as integer , increment 1.
     *
     * @param ConstraintEventFormat $event event class
     *
     * @return void
     */
    public function validate(ConstraintEventFormat $event)
    {
        $schema = $event->getSchema();

        if (!isset($schema->{'x-constraints'}) ||
            (is_array($schema->{'x-constraints'}) && !in_array('versioning', $schema->{'x-constraints'}))
        ) {
            return;
        }

        // get the current recor
        if ($currentRecord = $this->utils->getCurrentEntity()) {
            $formVersion = $event->getElement();
            /** @var \JsonSchema\Entity\JsonPointer $pointer */
            $pointer = $event->getPath();
            $accessor = PropertyAccess::createPropertyAccessor();
            $path = $this->utils->getNormalizedPathFromPointer($pointer);
            $storedVersion = $accessor->getValue($currentRecord, $path);
            if ($storedVersion !== $formVersion) {
                $this->version = $storedVersion;
                $event->addError('The version does not match, please update your data.');
                return;
            }
        }
        return;
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
