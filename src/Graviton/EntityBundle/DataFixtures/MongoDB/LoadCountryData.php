<?php

namespace Graviton\EntityBundle\DataFixtures\MongoDB;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\EntityBundle\Document\Country;

/**
 * Load countries from Resources/data/coutries.json into mongodb
 *
 * @category GravitonEntityBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class LoadCountryData implements FixtureInterface, ContainerAwareInterface
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
        $serializer = $this->container->get('graviton.entity.serializer');
        $loader = $this->container->get('graviton.entity.fixturedata.loader');

        $rawData = $loader->load(__DIR__.'/../../Resources/data/countries.json');
        $rawData = json_encode(json_decode($rawData)[1]);

        $data = $serializer->deserialize(
            $rawData,
            'array<Graviton\EntityBundle\Document\Country>',
            'json'
        );

        foreach ($data as $country) {
            $manager->persist($country);
        }
        $manager->flush();
    }
}
