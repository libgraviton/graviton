<?php
/**
 * ObjectSlicer class file
 */

namespace Graviton\RestBundle\Utils;

/**
 * Object slicer like MongoDb "select" operator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ObjectSlicer
{
    /**
     * Make slice
     *
     * @param object|array $value Value
     * @param string       $path  Path
     * @return object|array
     *
     * @throws \InvalidArgumentException
     */
    public function slice($value, $path)
    {
        if (!is_array($value) && !is_object($value)) {
            throw new \InvalidArgumentException('Value must be an array or an object');
        }

        return $this->sliceRecursive($value, explode('.', $path));
    }

    /**
     * Make and merge slices
     *
     * @param object|array $value Value
     * @param array        $paths Paths
     * @return object|array
     *
     * @throws \InvalidArgumentException
     */
    public function sliceMulti($value, array $paths)
    {
        if (!is_array($value) && !is_object($value)) {
            throw new \InvalidArgumentException('Value must be an array or an object');
        }
        if (empty($paths)) {
            throw new \InvalidArgumentException('Paths must contain at least one element');
        }

        $paths = array_unique($paths);
        $paths = $this->normalizePaths($paths);

        $parts = [];
        foreach ($paths as $path) {
            $parts[] = $this->sliceRecursive($value, explode('.', $path));
        }

        $result = array_shift($parts);
        foreach ($parts as $part) {
            $result = $this->mergeSliceRecursive($result, $part);
        }
        return $result;
    }

    /**
     * Make slice recursive
     *
     * @param object|array $value Value
     * @param array        $keys  Keys
     * @return object|array
     */
    private function sliceRecursive($value, array $keys)
    {
        if (is_array($value) && !empty($value) && array_keys($value) !== range(0, count($value) - 1)) {
            $value = (object) $value;
        }

        if (is_object($value)) {
            $result = new \stdClass();

            $key = array_shift($keys);
            if (property_exists($value, $key)) {
                if (empty($keys)) {
                    $result->{$key} = $value->{$key};
                } elseif (is_object($value->{$key}) || is_array($value->{$key})) {
                    $result->{$key} = $this->sliceRecursive($value->{$key}, $keys);
                }
            }

            return $result;
        } else {
            $result = [];
            foreach ($value as $subvalue) {
                if (empty($keys)) {
                    $result[] = $subvalue;
                } elseif (is_object($subvalue) || is_array($subvalue)) {
                    $result[] = $this->sliceRecursive($subvalue, $keys);
                }
            }
            return $result;
        }
    }

    /**
     * Merge slices
     *
     * @param object|array $dst Dst
     * @param object|array $src Src
     * @return object|array
     */
    private function mergeSliceRecursive($dst, $src)
    {
        if (is_object($dst)) {
            foreach ((array) $src as $key => $value) {
                if (!property_exists($dst, $key)) {
                    $dst->{$key} = $value;
                } elseif (is_object($value) || is_array($value)) {
                    $dst->{$key} = $this->mergeSliceRecursive($dst->{$key}, $value);
                }
            }
        } else {
            foreach ($src as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $a[$key] = $this->mergeSliceRecursive($dst[$key], $value);
                }
            }
        }

        return $dst;
    }

    /**
     * Normalize paths
     *
     * It removes duplicates:
     * ["a.b", "b.c.d", "c.d", "a", "b"] -> ["a", "b", "c.d"]
     *
     * @param array $paths Paths
     * @return array
     */
    private function normalizePaths(array $paths)
    {
        $result = $paths;
        foreach ($paths as $path) {
            $result = array_filter(
                $result,
                function ($item) use ($path) {
                    return strpos($item, $path.'.') !== 0;
                }
            );
        }
        return $result;
    }
}
