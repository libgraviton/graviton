<?php
/**
 * class scanner
 */

namespace Graviton\DocumentBundle\Annotation;

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
     * returns an annotation driver that can be used to list all documents
     *
     * @param array|null $directories directories to scan
     *
     * @return DocumentDriver driver
     */
    public static function getDocumentAnnotationDriver(?array $directories = null): DocumentDriver
    {
        if ($directories == null) {
            $classFinder = Finder::create()
                ->directories()
                ->in(Graviton::getBundleScanDir())
                ->name('Document')
                ->filter(
                    function (\SplFileInfo $file) {
                        return (
                            str_contains($file->getPathname(), 'Graviton')
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

        return new DocumentDriver($directories);
    }
}
