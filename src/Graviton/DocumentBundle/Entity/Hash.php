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
        $data = $this->getArrayCopy();
        if (empty($data)) {
            return (object) [];
        }

        return $data;
    }
}
