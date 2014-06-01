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
    /**
     * test extracting headers from string
     *
     * @return void
     */
    public function testFromString()
    {
        $uri = 'http://localhost/test/resource';
        $altUri = 'http://localhost/test/alternate';
        $tests = array(
            array("${uri}, ${altUri}"),
            array("<${uri}>, <${altUri}>"),
            array("<${uri}>,<${altUri}>"),
            array("<${uri}>; rel=self, <${altUri}>"),
            array("<${uri}>; rel=\"self\", <${altUri}>"),
            array("<${uri}>; rel='self', <${altUri}>"),
            array("<${uri}>; rel=\"schema\"; type=\"urn:uri\",<${altUri}>"),
        );
        foreach ($tests as $test) {
            $headerValue = $test[0];

            $linkHeaders = LinkHeader::fromString($headerValue)->all();

            $this->assertCount(2, $linkHeaders);

            $this->assertEquals($uri, $linkHeaders[0]->getUri());
            $this->assertEquals($altUri, $linkHeaders[1]->getUri());
        }
    }

    /**
     * test building of strings
     *
     * @return void
     */
    public function testToString()
    {
        $uri = 'http://localhost/test/resource';
        $headers = "<${uri}>,<${uri}>";
        $this->assertEquals($headers, (string) LinkHeader::fromString($headers));
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
