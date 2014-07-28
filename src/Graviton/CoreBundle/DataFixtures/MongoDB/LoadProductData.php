<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\CoreBundle\Document\Product;

/**
 * Load Product data fixtures into mongodb
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LoadProductData implements FixtureInterface
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
        $products = array(
            1 => 'Checking account',
            2 => 'Savings Account',
            3 => 'Money market account',
            4 => 'Mortgage',
            5 => 'Personal loan',
            6 => 'Mutual fund',
            7 => 'Revolving credit',
            8 => 'Business loan',
 
        );
        foreach ($products as $id => $name) {
            $product = new Product;
            $product->setId($id);
            $product->setName(array('en' => $name));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
