<?php
/**
 * Hash class file
 */

namespace Graviton\DocumentBundle\Entity;

/**
 * Special type for hash fields
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Hash extends \ArrayObject implements \JsonSerializable
{
    /**
     * Specify data which should be serialized to JSON
     *
     * @return object
     */
    public function jsonSerialize()
    {
        return (object) $this->arrayFilterRecursive($this->getArrayCopy());
    }

    /**
     * Clean up, remove empty positions of second level and above
     *
     * @param array $input to be cleaned up
     * @return array
     */
    private function arrayFilterRecursive($input)
    {
        if (empty($input)) {
            return [];
        }
        foreach ($input as &$value) {
            if (is_array($value) || is_object($value)) {
                $value = $this->arrayFilterRecursive($value);
            }
        }
        return array_filter($input, [$this, 'cleanUpArray']);
    }

    /**
     * Remove NULL values or Empty array object
     * @param mixed $var object field value
     * @return bool
     */
    private function cleanUpArray($var) {
        if ($var !== false) {
            return !empty($var);
        }
        return true;
    }
}
