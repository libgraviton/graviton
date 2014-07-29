<?php

namespace Graviton\PersonBundle\Document;

/**
 * Graviton\PersonBundle\Document\PersonContact
 *
 * @category PersonBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
     * Set uri
     *
     * @param string $uri uri to contact resource (starting with tel:, http:, ...)
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
