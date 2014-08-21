<?php

namespace Graviton\PersonBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection
     */
    protected $contacts = array();

    /**
     * construct
     *
     * @return Consultant
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
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
     * @param PersonContact $contact contact to add
     *
     * @return self
     */
    public function addContact(PersonContact $contact)
    {
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * Remove contact
     *
     * @param PersonContact $contact contact to remove
     *
     * @return self
     */
    public function removeContact(PersonContact $contact)
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    /**
     * Get contacts
     *
     * @return ArrayCollection $contacts
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
