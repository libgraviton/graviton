<?php
/**
 * our own annotation driver
 */

namespace Graviton\DocumentBundle\Annotation\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Graviton\Graviton;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentDriver extends AnnotationDriver
{

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
     * @param Reader $reader reader
     * @param null   $paths  paths
     */
    public function __construct($reader, $paths = null)
    {
        parent::__construct($reader, $paths);
        $this->cacheLocation = Graviton::getTransientCacheDir() . 'document_annotations';
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
            $idField = $this->reader->getPropertyAnnotation($property, Id::class);
            $field = $this->reader->getPropertyAnnotation($property, Field::class);
            $embedOne = $this->reader->getPropertyAnnotation($property, EmbedOne::class);
            $embedMany = $this->reader->getPropertyAnnotation($property, EmbedMany::class);
            $referenceOne = $this->reader->getPropertyAnnotation($property, ReferenceOne::class);
            $referenceMany = $this->reader->getPropertyAnnotation($property, ReferenceMany::class);

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
     * load class metadata - not used here!
     *
     * @param string        $className class name
     * @param ClassMetadata $metadata  metadata
     *
     * @return void
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        throw new \LogicException('Not implemented');
    }
}
