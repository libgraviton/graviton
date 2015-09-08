<?php
/**
 * FormDataMapper class file
 */

namespace Graviton\DocumentBundle\Service;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FormDataMapper implements FormDataMapperInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * Constructor
     *
     * @param array $mapping Field mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Convert request to form data
     *
     * @param string $request   Request data
     * @param string $className Document class
     * @return array
     */
    public function convertToFormData($request, $className)
    {
        $document = json_decode($request);
        if (!is_object($document)) {
            return [];
        }

        if (isset($this->mapping[$className])) {
            foreach ($this->mapping[$className] as $path => $name) {
                $this->mapField($document, $path, $name);
            }
        }
        return json_decode(json_encode($document), true);
    }

    /**
     * Recursive mapper to rename fields for form
     *
     * @param mixed  $item Item to map
     * @param string $path Field path
     * @param string $name rename field to ...
     * @return array
     */
    private function mapField($item, $path, $name)
    {
        if ($path === $name) {
            return;
        }

        if (is_array($item)) {
            if (strpos($path, '0.') === 0) {
                $subField = substr($path, 2);

                array_map(
                    function ($subItem) use ($subField, $name) {
                        $this->mapField($subItem, $subField, $name);
                    },
                    $item
                );
            }
        } elseif (is_object($item)) {
            if (($pos = strpos($path, '.')) !== false) {
                $topLevel = substr($path, 0, $pos);
                $subField = substr($path, $pos + 1);

                if (isset($item->$topLevel)) {
                    $this->mapField($item->$topLevel, $subField, $name);
                }
            } elseif (isset($item->$path) || property_exists($item, $path)) {
                $item->$name = $item->$path;
                unset($item->$path);
            }
        }
    }
}
