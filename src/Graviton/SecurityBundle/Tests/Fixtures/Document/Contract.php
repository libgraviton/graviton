<?php

namespace GravitonDyn\SecurityBundle\Tests\Fixtures\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;


/**
 * GravitonDyn\ContractBundle\Document\Contract
 *
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Contract implements TranslatableDocumentInterface
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var Date $deletedDate
     */
    protected $deletedDate;

    /**
     * @var string $number
     */
    protected $number;

    /**
     * @var \GravitonDyn\CodeBundle\Document\Code $contractType
     */
    protected $contractType;

    /**
     * @var \GravitonDyn\CustomerBundle\Document\Customer $customer
     */
    protected $customer;

    /**
     * @var \GravitonDyn\AccountBundle\Document\Account[] $account
     */
    protected $account;

    /**
     * constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->account = new ArrayCollection();
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

    public function getDeleteddate()
    {
        return $this->deletedDate;
    }

    /**
     * Get number
     *
     * @return string $number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set number
     *
     * @param string $number value for number
     *
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }


    /**
     * Get contractType
     *
     * @return \GravitonDyn\CodeBundle\Document\Code $contractType
     */
    public function getContracttype()
    {
        return $this->contractType;
    }

    /**
     * Set contractType
     *
     * @param \GravitonDyn\CodeBundle\Document\Code $contractType object for contractType
     *
     * @return self
     */
    public function setContracttype(\GravitonDyn\CodeBundle\Document\Code $contractType)
    {
        $this->contractType = $contractType;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \GravitonDyn\CustomerBundle\Document\Customer $customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set customer
     *
     * @param \GravitonDyn\CustomerBundle\Document\Customer $customer object for customer
     *
     * @return self
     */
    public function setCustomer(\GravitonDyn\CustomerBundle\Document\Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get account
     *
     * @return \GravitonDyn\AccountBundle\Document\Account[] $account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set account
     *
     * @param \GravitonDyn\AccountBundle\Document\Account[] $account object for account
     *
     * @return self
     */
    public function setAccount($account)
    {
        $this->account = new ArrayCollection($account);

        return $this;
    }

    /**
     * add element to account
     *
     * @param \GravitonDyn\AccountBundle\Document\Account $account object to add to account
     *
     * @return self
     */
    public function addAccount($account)
    {
        $this->account[] = $account;
    }

    /**
     * remove element from account
     *
     * @param \GravitonDyn\AccountBundle\Document\Account $account object to remove from account
     *
     * @return self
     */
    public function removeAccount(\GravitonDyn\AccountBundle\Document\Account $account)
    {
        $this->account->removeElement($account);

        return $this;
    }

    /**
     * return translatable field names
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array('');
    }
}
