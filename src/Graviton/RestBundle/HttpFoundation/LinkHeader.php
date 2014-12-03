<?php

namespace Graviton\RestBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

/**
 * Represents a Link header.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LinkHeader
{
    /**
     * @var LinkHeaderItem[]
     */
    private $items = array();

    /**
     * Constructor
     *
     * @param LinkHeaderItem[] $items link header items
     *
     * @return \Graviton\RestBundle\HttpFoundation\LinkHeader
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Builds a LinkHeader instance from a string.
     *
     * @param string $headerValue value of complete header
     *
     * @return LinkHeader
     */
    public static function fromString($headerValue)
    {
        return new self(
            array_map(
                function ($itemValue) use (&$index) {
                    $item = LinkHeaderItem::fromString(trim($itemValue));

                    return $item;
                },
                preg_split(
                    '/(?:(".+?"|(?:<*)http(?:.*?)|(?:[^,]+))(?:,|$))/',
                    $headerValue,
                    0,
                    PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                )
            )
        );
    }

    /**
     * get LinkHeader instance from response
     *
     * @param \Symfony\Component\HttpFoundation\Response $response response to get header from
     *
     * @return LinkHeader
     */
    public static function fromResponse(Response $response)
    {
        $header = $response->headers->get('Link');
        if (is_array($header)) {
            implode(',', $header);
        }

        return self::fromString($header);
    }

    /**
     * get all items
     *
     * @return LinkHeaderItem[]
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * add a LinkHeaderItem.
     *
     * @param LinkHeaderItem $item item to add
     *
     * @return LinkHeader
     */
    public function add(LinkHeaderItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Cast contents to string.
     *
     * @return string
     */
    public function __toString()
    {
        return implode(',', $this->items);
    }
}
