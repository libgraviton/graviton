<?php
/**
 * Graviton\PersonBundle\Document\PersonContact
 *
 * PHP Version 5
 *
 * @category PersonBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\PersonBundle\Document;

/**
 * Graviton\PersonBundle\Document\PersonContact
 *
 * @category PersonBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PersonContact
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var string $ri
     */
    protected $value;

    /**
     * @var string $protocol
     */
    protected $protocol;

    /**
     * @var string $uri
     */
    protected $uri;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type type of contact (ie. phone, email, web, xmpp)
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param string $value value to contact resource (starting with tel:, http:, ...)
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set protocol
     *
     * @param string $protocol protocol
     *
     * @return self
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return string $protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Set uri
     *
     * @param string $uri uri
     *
     * @return self
     */
    public function setUri($uri)
    {
         $this->uri = $uri;

         return $this;
    }

    /**
     * Get uri
     *
     * @return string $uri
     */
    public function getUri()
    {
        return $this->uri;
    }
}
