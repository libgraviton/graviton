<?php

namespace Graviton\TaxonomyBundle\DataFixtures\MongoDB;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Graviton\TaxonomyBundle\Document\Country;

/**
 * Load countries from Resources/data/coutries.json into mongodb
 *
 * @category GravitonTaxonomyBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        $serializer = $this->container->get('graviton.taxonomy.serializer');
        $loader = $this->container->get('graviton.taxonomy.fixturedata.loader');

        $rawData = $loader->load(__DIR__.'/../../Resources/data/countries.json');
        $rawData = json_encode(json_decode($rawData)[1]);

        $data = $serializer->deserialize(
            $rawData,
            'array<Graviton\TaxonomyBundle\Document\Country>',
            'json'
        );

        foreach ($data as $country) {
            $manager->persist($country);
        }
        $manager->flush();
    }
}
