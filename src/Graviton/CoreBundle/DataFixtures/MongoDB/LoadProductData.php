<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\CoreBundle\DataFixtures\MongoDB;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\CoreBundle\Document\Product;

/**
 * Load Product data fixtures into mongodb
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class LoadProductData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @private ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     *
     * @param ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
            array('id' => 1, 'name' => array('en' => 'Checking account')),
            array('id' => 2, 'name' => array('en' => 'Savings Account')),
            array('id' => 3, 'name' => array('en' => 'Money market account')),
            array('id' => 4, 'name' => array('en' => 'Mortgage')),
            array('id' => 5, 'name' => array('en' => 'Personal loan')),
            array('id' => 6, 'name' => array('en' => 'Mutual fund')),
            array('id' => 7, 'name' => array('en' => 'Revolving credit')),
            array('id' => 8, 'name' => array('en' => 'Business loan')),
        );
        $serializer = $this->container->get('serializer');

        $data = $serializer->deserialize(
            json_encode($products),
            'array<Graviton\CoreBundle\Document\Product>',
            'json'
        );

        foreach ($data as $product) {
            $manager->persist($product);
        }
        $manager->flush();
    }
}
