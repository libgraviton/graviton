<?php

namespace Graviton\RestBundle\Tests\HttpFoundation;

use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

/**
 * Tests LinkHeader.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LinkHeaderTest extends \PHPUnit_Framework_TestCase
{
    const URI = 'http://localhost/test/resource';
    const ALT_URI = 'http://localhost/test/alternate';

    /**
     * test extracting headers from string
     *
     * @dataProvider headerValueProvider
     *
     * @param  string $headerValue String to be transcoded to a \Graviton\RestBundle\HttpFoundation\LinkHeader
     * @return void
     *
     * @see headerValueProvider
     */
    public function testFromString($headerValue)
    {
        $linkHeaders = LinkHeader::fromString($headerValue)->all();

        $this->assertCount(2, $linkHeaders);

        $this->assertEquals(self::URI, $linkHeaders[0]->getUri());
        $this->assertEquals(self::ALT_URI, $linkHeaders[1]->getUri());
    }

    /**
     * Data provider for »testFromString« to make it more clear what headerValue caused a test to fail.
     *
     * @return array
     *
     * @see testFromString
     */
    public function headerValueProvider()
    {
        return array(
            'base URI' => array(self::URI . ',' . self::ALT_URI),
            'base URI encapsulated' => array('<'.self::URI.'>, <'.self::ALT_URI.'>'),
            'base URI encapsulated no space' => array('<'.self::URI.'>,<'.self::ALT_URI.'>'),
            'base URI, self linking, double quotes' => array('<'.self::URI.'>; rel="self", <'.self::ALT_URI.'>'),
            'base URI, self linking, single quotes' => array('<'.self::URI.">; rel='self', <".self::ALT_URI.'>'),
            'base URI with schema and type, double quotes' =>
                array('<'.self::URI.'>; rel="schema"; type="urn:uri",<'.self::ALT_URI .'>'),
        );
    }

    /**
     * test building of strings
     *
     * @dataProvider headerStringProvider
     *
     * @return void
     */
    public function testToString($headers)
    {
        $this->assertEquals($headers, (string) LinkHeader::fromString($headers));
    }

    /**
     * Data provider for »testToString« to make it more clear what header string caused a test to fail.
     *
     * @return array
     */
    public function headerStringProvider()
    {
        return array(
            'no items' => array(''),
            'one item' => array('<'.self::URI.'>'),
            'two items' => array('<'.self::URI.'>,<'.self::ALT_URI.'>'),
        );
    }

    /**
     * test adding an item
     *
     * @return void
     */
    public function testAddItem()
    {
        $header = new LinkHeader(array());
        $item = new LinkHeaderItem('urn:uri');

        $header->add($item);
        $links = $header->all();

        $this->assertSame($item, $links[0]);
    }
}
