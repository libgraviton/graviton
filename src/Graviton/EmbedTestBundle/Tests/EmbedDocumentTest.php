<?php
namespace Graviton\EmbedTestBundle\Tests;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\AppKernel;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\EmbedTestBundle\DataFixtures\MongoDB\LoadDocumentData;
use Graviton\EmbedTestBundle\Document\Document;
use Graviton\EmbedTestBundle\Document\Embedded;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class EmbeddedDocumentsTest extends WebTestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;
    /**
     * @var DocumentRepository
     */
    private $repo;

    public static function createKernel(array $options = [])
    {
        require_once __DIR__ . '/../../../../app/AppKernel.php';

        $kernel = new AppKernel('test', true);
        $kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));

        //set error reporting for phpunit
        ini_set('error_reporting', E_ALL);

        return $kernel;
    }

    public function setUp()
    {
        static::bootKernel();

        $this->dm = self::$kernel->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $this->repo = $this->dm->getRepository(Document::class);

        $this->loadFixtures([LoadDocumentData::class], null, 'doctrine_mongodb');
    }

    private function assertDocumentData($id, $name, $embedId, $embedName, array $many)
    {
        /** @var Document $original */
        $document = $this->repo->find('test');

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($id, $document->getId());
        $this->assertEquals($name, $document->getName());

        $this->assertInstanceOf(Embedded::class, $document->getEmbedded());
        $this->assertEquals($embedId, $document->getEmbedded()->getId());
        $this->assertEquals($embedName, $document->getEmbedded()->getName());

        $this->assertCount(count($many), $document->getEmbeddeds());
        $this->assertContainsOnlyInstancesOf(Embedded::class, $document->getEmbeddeds());
        foreach ($many as $i => $embed) {
            $this->assertArrayHasKey($i, $document->getEmbeddeds());
            $this->assertEquals($embed[0], $document->getEmbeddeds()[$i]->getId());
            $this->assertEquals($embed[1], $document->getEmbeddeds()[$i]->getName());
        }

        // clear document manager
        $this->dm->clear();
    }

    private function assertRawDocumentData($id, $name, $embedId, $embedName, array $many)
    {
        $this->assertEquals(
            [
                '_id' => $id,
                'name' => $name,
                'embedded' => [
                    '_id' => $embedId,
                    'name' => $embedName,
                ],
                'embeddeds' => array_map(function ($item) {
                    return [
                        '_id' => $item[0],
                        'name' => $item[1],
                    ];
                }, $many)
            ],
            $this->dm->getDocumentCollection(Document::class)->findOne(['_id' => 'test'])
        );
    }

    public function testDocument()
    {
        ///////////////////////////////////////////////////////////////////////
        // check document
        ///////////////////////////////////////////////////////////////////////
        $this->assertDocumentData(
            'test', 'original',
            'one', 'one',
            [['a', 'a']]
        );
        $this->assertRawDocumentData(
            'test', 'original',
            'one', 'one',
            [['a', 'a']]
        );

        ///////////////////////////////////////////////////////////////////////
        // update document
        ///////////////////////////////////////////////////////////////////////
        /** @var Document $updated */
        $updated = $this->repo->find('test');
        $updated
            ->setName('updated')
            ->setEmbedded(
                (new Embedded())
                    ->setId('two')
                    ->setName('two')
            )
            ->setEmbeddeds([
                (new Embedded())
                    ->setId('x')
                    ->setName('x')
            ]);
        $this->dm->persist($updated);
        $this->dm->flush();
        $this->dm->clear();

        $this->assertDocumentData(
            'test', 'updated',
            'two', 'two',
            [['x', 'x']]
        );
        $this->assertRawDocumentData(
            'test', 'updated',
            'two', 'two',
            [['x', 'x']]
        );

        ///////////////////////////////////////////////////////////////////////
        // upsert document
        ///////////////////////////////////////////////////////////////////////
        /** @var Document $upserted */
        $upserted = (new Document())
            ->setId('test')
            ->setName('upserted')
            ->setEmbedded(
                (new Embedded())
                    ->setId('three')
                    ->setName('three')
            )
            ->setEmbeddeds([]);
        $this->dm->persist($upserted);
        $this->dm->flush();
        $this->dm->clear();

        $this->assertDocumentData(
            'test', 'upserted',
            'three', 'three',
            []
        );
        $this->assertRawDocumentData(
            'test', 'upserted',
            'three', 'three',
            []
        );
    }
}
