<?php
/**
 * our own annotation driver
 */

namespace Graviton\DocumentBundle\Annotation\Driver;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Annotation;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentDriver
{

    use ColocatedMappingDriver;

    /**
     * DocumentDriver constructor.
     *
     * @param array $paths paths
     */
    public function __construct(array $paths)
    {
        $this->addPaths($paths);
    }

    /**
     * gets a field
     *
     * @param string $className class name
     *
     * @return array field annotation
     * @throws \ReflectionException
     */
    public function getFields($className): array
    {
        $refClass = new \ReflectionClass($className);
        $map = [];

        foreach ($refClass->getProperties() as $property) {
            $attributes = $this->getPropertyAttributes(
                $property->getAttributes(),
                [
                    Id::class,
                    Field::class,
                    EmbedOne::class,
                    EmbedMany::class,
                    ReferenceOne::class,
                    ReferenceMany::class
                ]
            );

            if (isset($attributes[Field::class])) {
                $map[$property->getName()] = $attributes[Field::class];
            } elseif (isset($attributes[Id::class])) {
                $map[$property->getName()] = $attributes[Id::class];
            } elseif (isset($attributes[EmbedOne::class])) {
                $map[$property->getName()] = $attributes[EmbedOne::class];
            } elseif (isset($attributes[EmbedMany::class])) {
                $map[$property->getName()] = $attributes[EmbedMany::class];
            } elseif (isset($attributes[ReferenceOne::class])) {
                $map[$property->getName()] = $attributes[ReferenceOne::class];
            } elseif (isset($attributes[ReferenceMany::class])) {
                $map[$property->getName()] = $attributes[ReferenceMany::class];
            }
        }

        return $map;
    }

    /**
     * is transient
     *
     * @param string $className classname
     * @return false
     */
    public function isTransient(string $className)
    {
        return false;
    }

    /**
     * getPropertyAttributes
     *
     * @param ?array $attributes       attributes
     * @param array  $attributesToFind attributes to find
     *
     * @return Annotation[] attributes
     */
    private function getPropertyAttributes(?array $attributes, array $attributesToFind) : array
    {
        if (!is_array($attributes)) {
            return [];
        }

        $attrs = [];
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), $attributesToFind)) {
                $attrs[$attribute->getName()] = $attribute->newInstance();
            }
        }

        return $attrs;
    }
}
