<?php
/**
 * Tests LinkHeader.
 */

namespace Graviton\RestBundle\Tests\HttpFoundation;

use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

/**
 * Tests LinkHeader.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
     * check if , works in link
     *
     * @return void
     */
    public function testCommaInLink()
    {
        $headers = LinkHeader::fromString('<http://localhost/core/test?limit(1%2C1)>')->all();
        $this->assertCount(1, $headers);
    }

    /**
     * test building of strings
     *
     * @dataProvider headerStringProvider
     *
     * @param string[] $headers headers to test against
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
