<?php

namespace Graviton\RestBundle\Tests\HttpFoundation;

use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

/**
 * Tests LinkHeaderItem.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LinkHeaderItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test extracting basic header from string
     *
     * @return void
     */
    public function testFromString()
    {
        $uri = 'http://localhost/test/resource';
        $tests = array(
            array($uri),
            array("<${uri}>"),
            array("<${uri}>; rel=\"self\"", array('rel' => 'self')),
            array("<${uri}>; rel=self", array('rel' => 'self')),
            array("<${uri}>; rel='self'", array('rel' => 'self')),
        );
        foreach ($tests AS $test) {
            $itemValue = $test[0];

            $linkHeaderItem = LinkHeaderItem::fromString($itemValue);

            $this->assertInstanceOf('Graviton\RestBundle\HttpFoundation\LinkHeaderItem', $linkHeaderItem);
            $this->assertEquals($uri, $linkHeaderItem->getUri());

            if (!empty($item[1]) && array_key_exists('rel', $test[1])) {
                $this->assertEquals($test[1]['rel'], $linkHeaderItem->getRel());
            }
        }
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
     * Test getting and setting rel attribute.
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
     * @return void
     */
    public function testToString()
    {
        $item = new LinkHeaderItem('http://localhost');

        $this->assertEquals('<http://localhost>', (string) $item);

        $item =  new LinkHeaderItem('http://localhost', array('rel' => 'self'));

        $this->assertEquals('<http://localhost>; rel="self"', (string) $item);
    }
}
