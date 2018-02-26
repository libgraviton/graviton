<?php
/**
 * Represents a Link header item.
 */

namespace Graviton\RestBundle\HttpFoundation;

/**
 * Represents a Link header item.
 *
 * Based on Symfony\Component\HttpFoundation\AcceptHeaderItem.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LinkHeaderItem
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $attributes = array();

    /**
     * Constructor.
     *
     * @param string $uri        uri value of item
     * @param array  $attributes array of attributes
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
     * @param string $itemValue value of a single link header
     *
     * @return \Graviton\RestBundle\HttpFoundation\LinkHeaderItem
     */
    public static function fromString($itemValue)
    {
        $bits = preg_split('/(".+?"|[^;]+)(?:;|$)/', $itemValue, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $value = array_shift($bits);
        $attributes = array();

        foreach ($bits as $bit) {
            list($bitName, $bitValue) = explode('=', trim($bit));

            $bitValue = self::trimEdge($bitValue, '"');
            $bitValue = self::trimEdge($bitValue, '\'');

            $attributes[$bitName] = $bitValue;
        }

        $url = self::trimEdge($value, '<');

        return new self($url, $attributes);
    }

    /**
     * cast item to string
     *
     * @return string
     */
    public function __toString()
    {
        $values = array('<'.$this->uri.'>');

        foreach ($this->attributes as $name => $value) {
            $values[] = sprintf('%s="%s"', $name, $value);
        }

        return implode('; ', $values);
    }

    /**
     * Get URI.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set a new URI.
     *
     * @param string $uri new URI value
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
     * @return string
     */
    public function getRel()
    {
        $relation = $this->getAttribute('rel');

        return empty($relation) ? '' : $relation;
    }

    /**
     * Set attribute
     *
     * @param string $name  attribute name
     * @param string $value attribute value
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
     * @param string $name attribute name
     *
     * @return string
     */
    public function getAttribute($name)
    {
        return empty($this->attributes[$name]) ? '' : $this->attributes[$name];
    }

    /**
     * trim edge of string if char maches
     *
     * @param string $string string
     * @param string $char   char
     *
     * @return string
     */
    private static function trimEdge($string, $char)
    {
        if (substr($string, 0, 1) == $char) {
            $string = substr($string, 1, -1);
        }

        return $string;
    }
}
