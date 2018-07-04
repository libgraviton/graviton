<?php
/**
 * ExtReference class file
 */

namespace Graviton\DocumentBundle\Entity;

/**
 * Our internal MongoDbRef representation
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReference implements \JsonSerializable
{
    /**
     * @var string Reference collection
     */
    private $ref;
    /**
     * @var string Document ID
     */
    private $id;

    /**
     * Create new extref
     *
     * @param string $ref Collection
     * @param string $id  ID
     * @return ExtReference
     */
    public static function create($ref, $id)
    {
        return (new ExtReference())->setRef($ref)->setId($id);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return ['$ref' => $this->ref, '$id' => $this->id];
    }

    /**
     * Get collection name
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set collection name
     *
     * @param string $ref Collection
     * @return $this
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
        return $this;
    }

    /**
     * Get document ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set document ID
     *
     * @param string $id ID
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
