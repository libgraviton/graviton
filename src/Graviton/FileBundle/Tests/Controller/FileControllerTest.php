<?php
/**
 * functional test for /file
 */

namespace Graviton\FileBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/file/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/file/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\FileBundle\DataFixtures\MongoDB\LoadFileData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * check for empty collections when no fixtures are loaded
     *
     * @return void
     */
    public function testFindAllEmptyCollection()
    {
        // reset fixtures since we already have some from setUp
        $this->loadFixtures(array(), null, 'doctrine_mongodb');
        $client = static::createRestClient();
        $client->request('GET', '/file');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);

        $this->assertEquals(array(), $results);
    }
}
