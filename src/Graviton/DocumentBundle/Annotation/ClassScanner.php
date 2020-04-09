<?php
/**
 * class scanner
 */

namespace Graviton\DocumentBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Graviton\DocumentBundle\Annotation\Driver\DocumentDriver;
use Graviton\Graviton;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ClassScanner
{

    /**
     * @var array all annotation we want to load
     */
    private static $relevantAnnotations = [
        ODM\Document::class,
        ODM\InheritanceType::class,
        ODM\Id::class,
        ODM\Field::class,
        ODM\Indexes::class,
        ODM\Index::class,
        ODM\EmbedOne::class,
        ODM\EmbedMany::class,
        ODM\ReferenceOne::class,
        ODM\ReferenceMany::class,
        ODM\MappedSuperclass::class,
        ODM\EmbeddedDocument::class
    ];

    /**
     * load all known annotations
     *
     * @return void
     */
    public static function loadAnnotations()
    {
        foreach (self::$relevantAnnotations as $className) {
            class_exists($className);
        }
    }

    /**
     * returns an annotation driver that can be used to list all documents
     *
     * @param array|null $directories directories to scan
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     *
     * @return DocumentDriver driver
     */
    public static function getDocumentAnnotationDriver(?array $directories = null)
    {
        self::loadAnnotations();

        if ($directories == null) {
            $classFinder = Finder::create()
                ->directories()
                ->in(Graviton::getBundleScanDir())
                ->name('Document')
                ->filter(
                    function (\SplFileInfo $file) {
                        return (
                            strpos($file->getPathname(), 'Graviton') !== false
                        );
                    }
                );

            $directories = array_map(
                function (\SplFileInfo $file) {
                    return $file->getPathname();
                },
                iterator_to_array($classFinder)
            );
        }

        return self::getDocumentDriver($directories);
    }

    /**
     * just return the document driver itself
     *
     * @param array $directories directories
     *
     * @return DocumentDriver driver
     */
    public static function getDocumentDriver(array $directories = [])
    {
        $annotationReader = new AnnotationReader();
        return new DocumentDriver($annotationReader, $directories);
    }
}
