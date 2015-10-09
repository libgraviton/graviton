<?php
namespace Graviton\EmbedTestBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\EmbedTestBundle\Document\Document;
use Graviton\EmbedTestBundle\Document\Embedded;

class LoadDocumentData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $document = (new Document())
            ->setId('test')
            ->setName('original')
            ->setEmbedded(
                (new Embedded())
                    ->setId('one')
                    ->setName('one')
            )
            ->addEmbedded(
                (new Embedded())
                    ->setId('a')
                    ->setName('a')
            );

        $manager->persist($document);
        $manager->flush();
    }
}
