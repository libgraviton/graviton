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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
