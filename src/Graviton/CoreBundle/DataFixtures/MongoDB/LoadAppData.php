<?php

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\CoreBundle\Document\App;

class LoadAppData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $helloApp = new App;
        $helloApp->setName('hello');
        $helloApp->setTitle('Hello World!');
        $helloApp->setShowInMenu(true);

        $manager->persist($helloApp);

        $adminApp = new App;
        $adminApp->setName('admin');
        $adminApp->setTitle('Administration');
        $adminApp->setShowInMenu(true);

        $manager->persist($adminApp);

        $manager->flush();
    }
}
