<?php
/**
 * functional test for /core/product
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/product.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ShowcaseControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/product/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/product/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * See how our missing fields are explained to us
     *
     * @return void
     */
    public function testMissingFields()
    {
        $document = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-incomplete.json'),
            true
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $expectedErrors = [];
        $expectedErrors[0] = new \stdClass();
        $expectedErrors[0]->property_path = "children[someOtherField]";
        $expectedErrors[0]->message = "This value is not valid.";
        $expectedErrors[1] = new \stdClass();
        $expectedErrors[1]->property_path = "data.contact.type";
        $expectedErrors[1]->message = "This value should not be blank.";
        $expectedErrors[2] = new \stdClass();
        $expectedErrors[2]->property_path = "data.contact.protocol";
        $expectedErrors[2]->message = "This value should not be blank.";
        $expectedErrors[3] = new \stdClass();
        $expectedErrors[3]->property_path = "data.contact.value";
        $expectedErrors[3]->message = "This value should not be blank.";
        $expectedErrors[4] = new \stdClass();
        $expectedErrors[4]->property_path = "data.contactCode.text";
        $expectedErrors[4]->message = "This value should not be blank.";

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
        );
    }

    /**
     * check if all fixtures are returned on GET
     *
     * @return void
     */
    public function testMinimalPost()
    {
        $fullDocument = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-minimal.json'),
            true
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase', $fullDocument);
        $response = $client->getResponse();

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));

        $response = $client->getResults();

        $this->assertJsonStringEqualsJsonString(
            json_encode($fullDocument),
            json_encode($response)
        );
    }
}
