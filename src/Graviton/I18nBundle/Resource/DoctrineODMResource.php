<?php

namespace Graviton\I18nBundle\Resource;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * Resource used by translator to keep check of db changes
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class DoctrineODMResource implements ResourceInterface, \Serializable
{
    /**
     * @var string
     */
    private $resource;

    /**
     * create Doctrine ODM resource
     *
     * @param string $resource path to info file
     *
     * @return DoctrineODMResource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getResource();
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritDoc}
     *
     * @param integer $timestamp The last time the resource was loaded
     *
     * @return boolean true if the resource has not been updated, false otherwise
     */
    public function isFresh($timestamp)
    {
        // @todo implement me based on an odm listener and timestamp in the .odm file
        return false;
    }

    /**
     * {@inheritDocs}
     *
     * @return string
     */
    public function serialize()
    {
        return $this->resource;
    }

    /**
     * {@inheritDocs}
     *
     * @param string $serialized data as serialized by serialize
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->resource = $serialized;
    }
}
