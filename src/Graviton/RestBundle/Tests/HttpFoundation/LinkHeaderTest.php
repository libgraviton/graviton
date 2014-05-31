<?php

namespace Graviton\RestBundle\Tests\HttpFoundation;

use Graviton\RestBundle\HttpFoundation\LinkHeader;

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
        $uri2 = 'http://localhost/test/alternate';
        $tests = array(
            array("${uri}, ${uri2}"),
            array("<${uri}>, <${uri2}>"),
            array("<${uri}>,<${uri2}>"),
            array("<${uri}>; rel=self, <${uri2}>"),
            array("<${uri}>; rel=\"self\", <${uri2}>"),
            array("<${uri}>; rel='self', <${uri2}>"),
            array("<${uri}>; rel=\"schema\"; type=\"urn:uri\",<${uri2}>"),
        );
        foreach ($tests AS $test) {
            $headerValue = $test[0];

            $linkHeaders = LinkHeader::fromString($headerValue)->all();

            $this->assertCount(2, $linkHeaders);

            $this->assertEquals($uri, $linkHeaders[0]->getUri());
            $this->assertEquals($uri2, $linkHeaders[1]->getUri());
        }
    }
}

