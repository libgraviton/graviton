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

    private function assertDocumentData($id, $name, $embedId, $embedName)
    {
        /** @var Document $original */
        $document = $this->repo->find('test');

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($id, $document->getId());
        $this->assertEquals($name, $document->getName());

        $this->assertInstanceOf(Embedded::class, $document->getEmbedded());
        $this->assertEquals($embedId, $document->getEmbedded()->getId());
        $this->assertEquals($embedName, $document->getEmbedded()->getName());

        // clear document manager
        $this->dm->clear();
    }

    private function assertRawDocumentData($id, $name, $embedId, $embedName)
    {
        $this->assertEquals(
            [
                '_id' => $id,
                'name' => $name,
                'embedded' => [
                    '_id' => $embedId,
                    'name' => $embedName,
                ],
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
            'one', 'one'
        );
        $this->assertRawDocumentData(
            'test', 'original',
            'one', 'one'
        );

        ///////////////////////////////////////////////////////////////////////
        // update document
        ///////////////////////////////////////////////////////////////////////
        $updated = $this->repo->find('test');
        $updated
            ->setName('updated')
            ->setEmbedded(
                (new Embedded())
                    ->setId('two')
                    ->setName('two')
            );
        $this->dm->persist($updated);
        $this->dm->flush();
        $this->dm->clear();

        $this->assertDocumentData(
            'test', 'updated',
            'two', 'two'
        );
        $this->assertRawDocumentData(
            'test', 'updated',
            'two', 'two'
        );

        ///////////////////////////////////////////////////////////////////////
        // upsert document
        ///////////////////////////////////////////////////////////////////////
        $upserted = (new Document())
            ->setId('test')
            ->setName('upserted')
            ->setEmbedded(
                (new Embedded())
                    ->setId('three')
                    ->setName('three')
            );
        $this->dm->persist($upserted);
        $this->dm->flush();
        $this->dm->clear();

        $this->assertDocumentData(
            'test', 'upserted',
            'three', 'three'
        );
        $this->assertRawDocumentData(
            'test', 'upserted',
            'three', 'three'
        );
    }
}
