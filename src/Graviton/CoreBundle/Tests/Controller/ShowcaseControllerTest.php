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
     * check if all fixtures are returned on GET
     *
     * @return void
     */
    public function testAllFeatures()
    {
        $fullDocument = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-complete.json'),
            true
        );

        $client = static::createRestClient();

        $client->post('/hans/showcase', $fullDocument);
        $response = $client->getResponse();

        //var_dump($response); die;
        //$results = $client->getResults();

        //
        //var_dump($client);

    }
}
