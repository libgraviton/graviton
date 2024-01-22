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
use Graviton\Graviton;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentDriver
{

    use ColocatedMappingDriver;

    /**
     * @var string cache location
     */
    private $cacheLocation;

    /**
     * @var array cache cache
     */
    private $classCache = [];

    /**
     * DocumentDriver constructor.
     *
     * @param array $paths paths
     */
    public function __construct(array $paths)
    {
        $this->cacheLocation = Graviton::getTransientCacheDir() . 'document_annotations';
        $this->addPaths($paths);
        $this->loadCache();
    }

    /**
     * get CacheLocation
     *
     * @return string CacheLocation
     */
    public function getCacheLocation()
    {
        return $this->cacheLocation;
    }

    /**
     * loads annotation cache if it exists
     *
     * @return void
     */
    private function loadCache()
    {
        if (file_exists($this->cacheLocation)) {
            $this->classCache = unserialize(file_get_contents($this->cacheLocation));
        }
    }

    /**
     * gets a field
     *
     * @param string $className class name
     *
     * @return array field annotation
     * @throws \ReflectionException
     */
    public function getFields($className)
    {
        if (isset($this->classCache[$className])) {
            return $this->classCache[$className];
        }

        $refClass = new \ReflectionClass($className);
        $map = [];

        foreach ($refClass->getProperties() as $property) {
            $attributes = $property->getAttributes();

            $idField = $this->getPropertyAttribute($attributes, Id::class);
            $field = $this->getPropertyAttribute($attributes, Field::class);
            $embedOne = $this->getPropertyAttribute($attributes, EmbedOne::class);
            $embedMany = $this->getPropertyAttribute($attributes, EmbedMany::class);
            $referenceOne = $this->getPropertyAttribute($attributes, ReferenceOne::class);
            $referenceMany = $this->getPropertyAttribute($attributes, ReferenceMany::class);

            if (!is_null($field)) {
                $map[$property->getName()] = $field;
            } elseif (!is_null($idField)) {
                $map[$property->getName()] = $idField;
            } elseif (!is_null($embedOne)) {
                $map[$property->getName()] = $embedOne;
            } elseif (!is_null($embedMany)) {
                $map[$property->getName()] = $embedMany;
            } elseif (!is_null($referenceOne)) {
                $map[$property->getName()] = $referenceOne;
            } elseif (!is_null($referenceMany)) {
                $map[$property->getName()] = $referenceMany;
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
     * returns the reflectionattribute or null
     *
     * @param ?array $attributes attributes
     * @param string $className  class name
     *
     * @return Annotation|null optional attribute
     */
    private function getPropertyAttribute(?array $attributes, string $className) : ?Annotation
    {
        if (!is_array($attributes)) {
            return null;
        }

        foreach ($attributes as $attribute) {
            if ($attribute->getName() == $className) {
                return $attribute->newInstance();
            }
        }

        return null;
    }
}
