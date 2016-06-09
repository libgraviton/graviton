<?php
/**
 * ExtReferenceHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventFormat;
use Graviton\RestBundle\Routing\Loader\ActionUtils;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * JMS serializer handler for ExtReference
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceHandler
{
    /**
     * @var ExtReferenceConverterInterface
     */
    private $converter;

    /**
     * @var array
     */
    private $extRefPatternCache = [];

    /**
     * Constructor
     *
     * @param ExtReferenceConverterInterface $converter Converter
     */
    public function __construct(ExtReferenceConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Serialize extref to JSON
     *
     * @param JsonSerializationVisitor $visitor      Visitor
     * @param ExtReference             $extReference Extref
     * @param array                    $type         Type
     * @param Context                  $context      Context
     * @return string|null
     */
    public function serializeExtReferenceToJson(
        JsonSerializationVisitor $visitor,
        ExtReference $extReference,
        array $type,
        Context $context
    ) {
        try {
            return $visitor->visitString($this->converter->getUrl($extReference), $type, $context);
        } catch (\InvalidArgumentException $e) {
            return $visitor->visitNull(null, $type, $context);
        }
    }

    /**
     * Serialize extref to JSON
     *
     * @param JsonDeserializationVisitor $visitor Visitor
     * @param string                     $url     Extref URL
     * @param array                      $type    Type
     * @param Context                    $context Context
     * @return ExtReference|null
     */
    public function deserializeExtReferenceFromJson(
        JsonDeserializationVisitor $visitor,
        $url,
        array $type,
        Context $context
    ) {
        try {
            return $this->converter->getExtReference(
                $visitor->visitString($url, $type, $context)
            );
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Schema validation function for extref values. Will be executed when a user submits an extref
     *
     * @param ConstraintEventFormat $event event
     *
     * @return void
     */
    public function validateExtRef(ConstraintEventFormat $event)
    {
        $schema = $event->getSchema();

        if (!isset($schema->format) || (isset($schema->format) && $schema->format != 'extref')) {
            return;
        }

        $value = $event->getElement();

        // 1st) can it be converted to extref?
        try {
            $this->converter->getExtReference(
                $value
            );
        } catch (\InvalidArgumentException $e) {
            $event->addError(sprintf('Value "%s" is not a valid extref.', $value));
            return;
        }

        // 2nd) if yes, correct collection(s)?
        $collections = $schema->{'x-collection'};

        if (in_array('*', $collections)) {
            return;
        }

        $allValues = implode('-', $collections);
        if (!isset($this->extRefPatternCache[$allValues])) {
            $paths = [];
            foreach ($collections as $url) {
                $urlParts = parse_url($url);
                $paths[] = str_replace('/', '\\/', $urlParts['path']);
            }
            $this->extRefPatternCache[$allValues] = '(' . implode('|', $paths) . ')(' . ActionUtils::ID_PATTERN . ')$';
        }

        $stringConstraint = $event->getFactory()->createInstanceFor('string');
        $schema->format = null;
        $schema->pattern = $this->extRefPatternCache[$allValues];
        $stringConstraint->check($value, $schema, $event->getPath());

        if (!empty($stringConstraint->getErrors())) {
            $event->addError(sprintf('Value "%s" does not refer to a correct collection for this extref.', $value));
        }
    }
}
