<?php
/**
 * test class for the "restricted fields" feature - fixed conditions on data
 */
namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\RestTestCase;
use GravitonDyn\SecurityUserBundle\DataFixtures\MongoDB\LoadSecurityUserData;
use GravitonDyn\TestCaseRestrictedFieldsBundle\DataFixtures\MongoDB\LoadTestCaseRestrictedFieldsData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictedFieldsWhoIsTest extends RestTestCase
{

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadTestCaseRestrictedFieldsData::class,
                LoadSecurityUserData::class
            ]
        );
    }

    /**
     * test data we see as anonymous user
     *
     * @return void
     */
    public function testWhoisHandlerAnonymousData()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/restricted-fields/');

        $this->assertSame(1, count($client->getResults()));
        $this->assertSame("100", $client->getResults()[0]->id);
    }

    /**
     * test data we see as hans user
     *
     * @return void
     */
    public function testWhoisHandlerHansData()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/restricted-fields/', [], [], ['HTTP_X-GRAVITON-USER' => 'HANS']);

        $this->assertSame(1, count($client->getResults()));
        $this->assertSame("200", $client->getResults()[0]->id);
    }

    /**
     * test data we see as fred user
     *
     * @return void
     */
    public function testWhoisHandlerFredData()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/restricted-fields/', [], [], ['HTTP_X-GRAVITON-USER' => 'FRED']);

        $this->assertSame(0, count($client->getResults()));
    }

    /**
     * make sure we cannot negate the fixed restriction
     *
     * @return void
     */
    public function testWhoisHandlerNegationOr()
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/testcase/restricted-fields/?or(eq(username,hans),ne(username,anonymous))',
            [],
            [],
            ['HTTP_X-GRAVITON-USER' => 'FRED']
        );

        $this->assertSame(0, count($client->getResults()));
    }

    /**
     * make sure we cannot negate the fixed restriction
     *
     * @return void
     */
    public function testWhoisHandlerNegationAndNe()
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/testcase/restricted-fields/?ne(username,fred)',
            [],
            [],
            ['HTTP_X-GRAVITON-USER' => 'FRED']
        );

        $this->assertSame(0, count($client->getResults()));
    }

    /**
     * make sure we cannot negate the fixed restriction
     *
     * @return void
     */
    public function testWhoisHandlerNegationAndEq()
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/testcase/restricted-fields/?eq(username,hans)',
            [],
            [],
            ['HTTP_X-GRAVITON-USER' => 'FRED']
        );

        $this->assertSame(0, count($client->getResults()));
    }
}
