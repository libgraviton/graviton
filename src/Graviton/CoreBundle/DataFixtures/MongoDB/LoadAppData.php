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
        $tabletApp = new App;
        $tabletApp->setId('tablet');
        $tabletApp->setName('Tablet');
        $tabletApp->setShowInMenu(true);
        $tabletApp->setOrder(1);

        $manager->persist($tabletApp);

        $adminApp = new App;
        $adminApp->setId('admin');
        $adminApp->setName('Administration');
        $adminApp->setShowInMenu(true);
        $adminApp->setOrder(2);

        $manager->persist($adminApp);

        $manager->flush();
    }
}
