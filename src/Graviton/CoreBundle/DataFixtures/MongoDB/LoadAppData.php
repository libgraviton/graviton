<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Doctrine\Persistence\ObjectManager;
use Graviton\MongoDB\Fixtures\FixtureInterface;
use Graviton\DocumentBundle\Entity\Translatable;
use GravitonDyn\AppBundle\Document\App;

/**
 * Load App data fixtures into mongodb
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
        $tabletApp = new App();
        $tabletApp->setId('tablet');
        $tabletApp->setName(
            Translatable::createFromTranslations(
                [
                    'en' => 'Tablet',
                    'de' => 'Tablet'
                ]
            )
        );
        $tabletApp->setShowInMenu(true);
        $tabletApp->setOrder(1);

        $manager->persist($tabletApp);

        $adminApp = new App();
        $adminApp->setId('admin');
        $adminApp->setName(
            Translatable::createFromTranslations(
                [
                    'en' => 'Administration',
                    'de' => 'Die Administration'
                ]
            )
        );
        $adminApp->setShowInMenu(true);
        $adminApp->setOrder(2);

        $manager->persist($adminApp);

        $manager->flush();
    }
}
