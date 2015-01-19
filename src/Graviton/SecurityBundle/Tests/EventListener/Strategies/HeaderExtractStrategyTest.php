<?php

namespace Graviton\SecurityBundle\EventListener\Strategies;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HeaderExtractStrategyTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class HeaderExtractStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyExpectingException()
    {
        $noRequestObject = new \stdClass();
        $strategy = new HeaderExtractStrategy();

        $this->setExpectedException(
            '\InvalidArgumentException',
            'Provided data to be scanned for authentication is not a \Symfony\Component\HttpFoundation\Request',
            Response::HTTP_BAD_REQUEST
        );

        $strategy->apply($noRequestObject);
    }

    public function testGetId()
    {
        $strategy = new HeaderExtractStrategy();

        $this->assertEquals(
            '\Graviton\SecurityBundle\EventListener\HeaderExtractStrategy',
            $strategy->getId()
        );
    }

    /**
     * @dataProvider stringProvider
     */
    public function testApply($headerFieldValue)
    {
        $strategy = new HeaderExtractStrategy();

        $server = array(
            'HTTP_X_IDP_USERNAMEINHALT' => $headerFieldValue, //"example-authentication-header",
        );

        $request = new Request(array(), array(), array(), array(), array(), $server);

        $strategy->apply($request);
    }

    public function stringProvider()
    {
        return array(
            'plain string, no special chars' => array('exampleAuthenticationHeader'),
            'string with special chars' => array("$-_.+!*'(),{}|\\^~[]`<>#%;/?:@&=."),
            'string with octal chars' => array("a: \141, A: \101"),
            'string with hex chars' => array("a: \x61, A: \x41")
        );
    }
}
