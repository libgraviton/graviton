<?php

namespace Graviton\PersonBundle\DataFixtures\MongoDB;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\PersonBundle\Document\Consultant;

/**
 * Load a example consultant and loads of fake data
 *
 * @category PersonBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LoadConsultantData implements FixtureInterface, ContainerAwareInterface
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
        $serializer = $this->container->get('serializer');
        $faker = $this->container->get('test.faker');

        for ($i = 0; $i < 15; $i++) {
            $faker->seed($i);
            $contacts = $this->generateContacts($faker);
            $consultant = array(
                'id' => strtoupper($faker->bothify('????###??')),
                'firstName' => $faker->firstName,
                'lastName' => $faker->lastName,
                'contacts' => $contacts
            );
            $manager->persist(
                $serializer->deserialize(
                    json_encode($consultant),
                    'Graviton\PersonBundle\Document\Consultant',
                    'json'
                )
            );
        }
        $manager->flush();
    }

    /**
     * get fake contact info
     *
     * @param \Faker\Generator $faker seeded faker
     *
     * @return object[]
     */
    private function generateContacts(\Faker\Generator $faker)
    {
        $mailto = $faker->email;
        $tel = $faker->phoneNumber;
        $fax = $faker->phoneNumber;
        $url = $faker->url;
        list($urlProto) = explode(':', $url);
        return array(
            array(
                'type' => 'business',
                'protocol' => 'mailto',
                'value' => $mailto,
                '$ref' => 'mailto:'.$mailto,
            ),
            array(
                'type' => 'business',
                'protocol' => 'tel',
                'value' => $tel,
                '$ref' => 'tel:'.$tel,
            ),
            array(
                'type' => 'business',
                'protocol' => 'fax',
                'value' => $fax,
                '$ref' => 'fax:'.$fax,
            ),
            array(
                'type' => 'business',
                'protocol' => $urlProto,
                'value' => $url,
                '$ref' => $url,
            ),
        );
    }
}
