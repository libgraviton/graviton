<?php

namespace Graviton\RestBundle\HttpFoundation;

/**
 * Represents a Link header item.
 *
 * Based on Symfony\Component\HttpFoundation\AcceptHeaderItem.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LinkHeaderItem
{
    /**
     * @var String
     */
    private $uri;

    /**
     * @var Array
     */
    private $attributes = array();

    /**
     * Constructor.
     *
     * @param String $uri        uri value of item
     * @param Array  $attributes
     *
     * @return void
     */
    public function __construct($uri, array $attributes = array())
    {
        $this->uri = $uri;

        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Builds a LinkHeaderItem instance from a string.
     *
     * @param String $itemValue value of a single link header
     *
     * @return LinkHeaderItem
     */
    public static function fromString($itemValue)
    {
        $bits = preg_split('/\s*(?:;*("[^"]+");*|;*(\'[^\']+\');*|;+)\s*/', $itemValue, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $value = array_shift($bits);
        $attributes = array();

        $lastNullAttribute = null;
        foreach ($bits as $bit) {
            if (($start = substr($bit, 0, 1)) === ($end = substr($bit, -1)) && ($start === '"' || $start === '\'')) {
                $attributes[$lastNullAttribute] = substr($bit, 1, -1);
            } elseif ('=' === $end) {
                $lastNullAttribute = $bit = substr($bit, 0, -1);
                $attributes[$bit] = null;
            } else {
                $parts = explode('=', $bit);
                $attributes[$parts[0]] = isset($parts[1]) && strlen($parts[1]) > 0 ? $parts[1] : '';
            }
        }

        $url = $value;
        if (substr($value, 0, 1) == '<') {
            $url = substr($value, 1, -1);
        }

        return new self($url, $attributes);
    }

    /**
     * Get URI.
     *
     * @return String
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set a new URI.
     *
     * @param String $uri new URI value
     *
     * @return LinkHeaderItem
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get rel attribute
     *
     * @return String
     */
    public function getRel()
    {
        return $this->getAttribute('rel');
    }

    /**
     * Set attribute
     *
     * @param String $name  attribute name
     * @param String $value attribute value
     *
     * @return LinkHeaderItem
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Get an attribute.
     *
     * @param String $name attirbute name
     *
     * @return String
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }
}

