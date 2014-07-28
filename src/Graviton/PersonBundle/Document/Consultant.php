<?php

namespace Graviton\PersonBundle\Document;

/**
 * Graviton\PersonBundle\Document\Consultant
 *
 * @category PersonBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class Consultant
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $firstName
     */
    protected $firstName;

    /**
     * @var string $lastName
     */
    protected $lastName;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var Graviton\PersonBundle\Document\PersonContact[]
     */
    protected $contacts = array();

    public function __construct()
    {
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Add contact
     *
     * @param Graviton\PersonBundle\Document\PersonContact $contact
     */
    public function addContact(\Graviton\PersonBundle\Document\PersonContact $contact)
    {
        $this->contacts[] = $contact;
    }

    /**
     * Remove contact
     *
     * @param Graviton\PersonBundle\Document\PersonContact $contact
     */
    public function removeContact(\Graviton\PersonBundle\Document\PersonContact $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
     * Get contacts
     *
     * @return Doctrine\Common\Collections\Collection $contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }
}
