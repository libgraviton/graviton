<?php
/**
 * ExtReferenceHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventFormat;
use Graviton\RestBundle\Routing\Loader\ActionUtils;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * JMS serializer handler for ExtReference
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceHandler
{
    /**
     * @var ExtReferenceConverter
     */
    private $converter;

    /**
     * @var array
     */
    private $extRefPatternCache = [];

    /**
     * Constructor
     *
     * @param ExtReferenceConverter $converter Converter
     */
    public function __construct(ExtReferenceConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Serialize extref to JSON
     *
     * @param SerializationVisitorInterface $visitor      Visitor
     * @param ExtReference                  $extReference Extref
     * @param array                         $type         Type
     * @param Context                       $context      Context
     * @return string|null
     */
    public function serializeExtReferenceToJson(
        SerializationVisitorInterface $visitor,
        ExtReference $extReference,
        array $type,
        Context $context
    ) {
        if (null === $extReference && !$context->shouldSerializeNull()) {
            throw new NotAcceptableException();
        }

        if (null === $extReference) {
            return $visitor->visitNull(null, $type);
        }

        try {
            return $this->converter->getUrl($extReference);
        } catch (\InvalidArgumentException $e) {
            return $visitor->visitNull(null, $type);
        }
    }

    /**
     * Serialize extref to JSON
     *
     * @param DeserializationVisitorInterface $visitor Visitor
     * @param string                          $url     Extref URL
     * @param array                           $type    Type
     * @param Context                         $context Context
     * @return ExtReference|null
     */
    public function deserializeExtReferenceFromJson(
        DeserializationVisitorInterface $visitor,
        $url,
        array $type,
        Context $context
    ) {
        if (null === $url && !$context->shouldSerializeNull()) {
            throw new NotAcceptableException();
        }

        if (null === $url) {
            return $visitor->visitNull(null, $type);
        }

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
        if (!isset($schema->{'x-collection'})) {
            return;
        }

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
