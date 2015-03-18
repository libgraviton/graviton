<?php
/**
 * Tests LinkHeaderItem.
 */

namespace Graviton\RestBundle\Tests\HttpFoundation;

use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

/**
 * Tests LinkHeaderItem.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LinkHeaderItemTest extends \PHPUnit_Framework_TestCase
{
    const URI = 'http://localhost/test/resource';

    /**
     * test extracting basic header from string
     *
     * @dataProvider headerValueProvider
     *
     * @param string $itemValue String to be transcoded to a \Graviton\RestBundle\HttpFoundation\LinkHeaderItem
     * @param string $relation  Name of the relation defined by the $itemValue
     *
     * @return void
     */
    public function testFromString($itemValue, $relation = '')
    {
        $linkHeaderItem = LinkHeaderItem::fromString($itemValue);

        $this->assertInstanceOf('Graviton\RestBundle\HttpFoundation\LinkHeaderItem', $linkHeaderItem);
        $this->assertEquals(self::URI, $linkHeaderItem->getUri());

        $this->assertEquals($relation, $linkHeaderItem->getRel());
    }

    /**
     * data provider for »testFromString« to make it more clear what $itemValue caused a test to fail.
     *
     * @return array
     *
     * @see testFromString
     */
    public function headerValueProvider()
    {
        return array(
            'base URI'                              => array(self::URI),
            'base URI encapsulated'                 => array('<'.self::URI.'>'),
            'base URI, self linking, no quotes'     => array('<'.self::URI.'>; rel=self', 'self'),
            'base URI, self linking, double quotes' => array('<'.self::URI.'>; rel="self"', 'self'),
            'base URI, self linking, single quotes' => array('<'.self::URI.">; rel='self'", 'self'),
        );
    }

    /**
     * test setting and getting uri
     *
     * @return void
     */
    public function testGetSetUri()
    {
        $uri = 'http://localhost/test/test';
        $linkHeaderItem = new LinkHeaderItem($uri);

        $this->assertEquals($uri, $linkHeaderItem->getUri());

        $uri = $uri.'?test=true';
        $linkHeaderItem->setUri($uri);

        $this->assertEquals($uri, $linkHeaderItem->getUri());
    }

    /**
     * test getting and setting rel attribute.
     *
     * @return void
     */
    public function testGetSetRel()
    {
        $linkHeaderItem = new LinkHeaderItem('urn:test', array('rel' => 'self'));

        $this->assertEquals('self', $linkHeaderItem->getRel());

        $linkHeaderItem->setAttribute('rel', 'parent');

        $this->assertEquals('parent', $linkHeaderItem->getRel());
    }

    /**
     * test string conversion.
     *
     * @dataProvider linkHeaderItemParameterProvider
     *
     * @param string  $expected   expected string
     * @param string  $uri        uri to base item on
     * @param array[] $attributes attributes for LinkHeaderItem
     *
     * @return void
     */
    public function testToString($expected, $uri, array $attributes = array())
    {
        $item = new LinkHeaderItem($uri, $attributes);

        $this->assertEquals($expected, (string) $item);
    }

    /**
     * data provider for »testToString« to make it more clear what $uri/$attributes combination caused a test to fail.
     *
     * @return array
     */
    public function linkHeaderItemParameterProvider()
    {
        return array(
            'uri only'            => array('<http://localhost>', 'http://localhost'),
            'uri plus attribute'  =>
                array(
                    '<http://localhost>; rel="self"',
                    'http://localhost',
                    array('rel' => 'self')
                ),
                'uri plus attributes' =>
                array(
                    '<http://localhost>; rel="schema"; type="urn:uri"',
                    'http://localhost',
                    array('rel' => 'schema', 'type' => 'urn:uri')
                ),
        );
    }
}
