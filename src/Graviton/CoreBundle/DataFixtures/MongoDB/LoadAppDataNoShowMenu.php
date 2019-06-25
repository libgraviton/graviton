<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\DocumentBundle\Entity\Translatable;
use GravitonDyn\AppBundle\Document\App;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LoadAppDataNoShowMenu implements FixtureInterface
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
        $tabletApp = new App();
        $tabletApp->setId('otherapp');
        $tabletApp->setName(Translatable::createFromOriginalString('otherApp'));
        $tabletApp->setShowInMenu(false);
        $tabletApp->setOrder(999);

        $manager->persist($tabletApp);

        $adminApp = new App();
        $adminApp->setId('otherapp2');
        $adminApp->setName(Translatable::createFromOriginalString('otherApp2'));
        $adminApp->setShowInMenu(false);
        $adminApp->setOrder(2);

        $manager->persist($adminApp);

        $manager->flush();
    }
}
