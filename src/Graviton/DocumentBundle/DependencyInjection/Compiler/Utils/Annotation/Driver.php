<?php
/**
 * our own annotation driver
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Annotation;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Driver extends AnnotationDriver
{
    /**
     * only return those that have the MongoDB Document annotation
     *
     * @param string $className class name
     *
     * @return bool true if yes, false otherwise
     */
    public function isTransient($className)
    {
        $reflectionClass = new \ReflectionClass($className);

        return (
            $this->reader->getClassAnnotation($reflectionClass, Document::class) === null &&
            $this->reader->getClassAnnotation($reflectionClass, EmbeddedDocument::class) === null
        );
    }

    public function getFields($className)
    {
        $refClass = new \ReflectionClass($className);
        $map = [];

        foreach ($refClass->getProperties() as $property) {
            $field = $this->reader->getPropertyAnnotation($property, Field::class);
            $embedOne = $this->reader->getPropertyAnnotation($property, EmbedOne::class);
            $embedMany = $this->reader->getPropertyAnnotation($property, EmbedMany::class);
            $referenceOne = $this->reader->getPropertyAnnotation($property, ReferenceOne::class);
            $referenceMany = $this->reader->getPropertyAnnotation($property, ReferenceMany::class);

            if (!is_null($field)) {
                $map[$property->getName()] = $field;
            } else if (!is_null($embedOne)) {
                $map[$property->getName()] = $embedOne;
            } else if (!is_null($embedMany)) {
                $map[$property->getName()] = $embedMany;
            } else if (!is_null($referenceOne)) {
                $map[$property->getName()] = $referenceOne;
            } else if (!is_null($referenceMany)) {
                $map[$property->getName()] = $field;
            }
        }

        return $map;
    }

    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        throw new \LogicException('Not implemented');
    }
}
