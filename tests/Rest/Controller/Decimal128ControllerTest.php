<?php
/**
 * Decimal128ControllerTest
 */

namespace Graviton\Tests\Rest\Controller;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Graviton\Tests\RestTestCase;
use MongoDB\BSON\Decimal128;
/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Decimal128ControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $repoId = 'gravitondyn.testcasedecimal128serialization.repository.testcasedecimal128serialization';
        if ($this->getContainer()->has($repoId)) {
            /**
             * @var $repo DocumentRepository
             */
            $repo = $this->getContainer()->get($repoId);

            $coll = $repo->getDocumentManager()->getDocumentCollection($repo->getClassName());
            $coll->deleteMany([]);

            //$coll = $dm->getDocumentCollection('TestCaseDecimal128Serialization');

            $doc1 = [];
            $doc1['_id'] = 'rec1';
            $doc1['amount'] = 20002.22;
            $coll->insertOne($doc1, ['upsert' => true]);

            $doc2 = [];
            $doc2['_id'] = 'rec2';
            $doc2['amount'] = new Decimal128('12899999999.505');

            $coll->insertOne($doc2, ['upsert' => true]);
        }

    }

    public function testDecimal128Serialization() {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/decimal128-deserialization/');

        $result = $client->getResults();
        $floats = [];
        foreach ($result as $document) {
            $floats[$document->id] = $document->amount;
        }

        $this->assertEquals(
            20002.22,
            $floats['rec1']
        );
        $this->assertEquals(
            12899999999.505,
            $floats['rec2']
        );
    }

}
