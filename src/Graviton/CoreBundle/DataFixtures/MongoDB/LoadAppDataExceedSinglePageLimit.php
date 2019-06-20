<?php
/**
 * /core/app fixtures for mongodb app collection, exceeding one page size.
 */

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GravitonDyn\AppBundle\Document\App;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LoadAppDataExceedSinglePageLimit implements FixtureInterface
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
        $howManyApps = 15;
        $createdCount = 0;

        while ($createdCount < $howManyApps) {
            $app = new App;
            $app->setId('app-'.$createdCount);
            $app->setName('App'.$createdCount);
            $app->setShowInMenu(true);
            $app->setOrder($createdCount);

            $manager->persist($app);
            $createdCount++;
        }

        $manager->flush();
    }
}
