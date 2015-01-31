<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\CoreBundle\Document\App;

/**
 * Load App data fixtures into mongodb
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class LoadAppData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ObjectManager $manager Object Manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $helloApp = new App;
        $helloApp->setId('hello');
        $helloApp->setTitle('Hello World!');
        $helloApp->setShowInMenu(true);

        $manager->persist($helloApp);

        $adminApp = new App;
        $adminApp->setId('admin');
        $adminApp->setTitle('Administration');
        $adminApp->setShowInMenu(true);

        $manager->persist($adminApp);

        $manager->flush();
    }
}
