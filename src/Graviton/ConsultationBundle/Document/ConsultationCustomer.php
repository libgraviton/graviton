<?php
/**
 * customer consultation relationship
 */

namespace Graviton\ConsultationBundle\Document;

/**
 * Graviton\ConsultationBundle\Document\ConsultationCustomer
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ConsultationCustomer
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $customerId
     */
    protected $customerId;

    /**
     * @var string $customerNumber
     */
    protected $customerNumber;

    /**
     * @var collection $salutation
     */
    protected $salutation;

    /**
     * @var string $firstName
     */
    protected $firstName;

    /**
     * @var string $lastName
     */
    protected $lastName;

    /**
     * @var string $clientAdvisor
     */
    protected $clientAdvisor;

    /**
     * @var string $customerType
     */
    protected $customerType;

    /**
     * @var string $domicile
     */
    protected $domicile;

    /**
     * @var date $customerOpeningDate
     */
    protected $customerOpeningDate;

    /**
     * @var collection $nationality
     */
    protected $nationality;

    /**
     * @var collection $sex
     */
    protected $sex;

    /**
     * @var date $birthDate
     */
    protected $birthDate;

    /**
     * @var string $bankAccount
     */
    protected $bankAccount;

    /**
     * @var collection $maritalStatus
     */
    protected $maritalStatus;

    /**
     * @var string $education
     */
    protected $education;

    /**
     * @var collection $employmentStatus
     */
    protected $employmentStatus;

    /**
     * @var collection $profession
     */
    protected $profession;

    /**
     * @var string $rating
     */
    protected $rating;

    /**
     * @var float $portfolioValue
     */
    protected $portfolioValue;

    /**
     * @var float $totalAssets
     */
    protected $totalAssets;

    /**
     * @var string $strategy
     */
    protected $strategy;

    /**
     * @var string $assetManagementCode
     */
    protected $assetManagementCode;

    /**
     * @var string $performance
     */
    protected $performance;

    /**
     * @var collection $taxDomicil
     */
    protected $taxDomicil;

    /**
     * @var collection $qiBasket
     */
    protected $qiBasket;

    /**
     * @var collection $euTaxLiability
     */
    protected $euTaxLiability;

    /**
     * @var collection $mifidCategory
     */
    protected $mifidCategory;

    /**
     * @var boolean $BVGCode
     */
    protected $BVGCode;

    /**
     * @var collection $customerCategory
     */
    protected $customerCategory;

    /**
     * @var collection $mainType
     */
    protected $mainType;

    /**
     * @var collection $customerSegment
     */
    protected $customerSegment;

    /**
     * @var collection $language
     */
    protected $language;

    /**
     * @var collection $clientSpecificValues
     */
    protected $clientSpecificValues;

    /**
     * @var collection $documents
     */
    protected $documents;

    /**
     * @var collection $legalAddress
     */
    protected $legalAddress;

    /**
     * @var collection $contacts
     */
    protected $contacts;

    /**
     * @var collection $relations
     */
    protected $relations;

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
     * Set customerId
     *
     * @param string $customerId customerId
     *
     * @return self
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return string $customerId customerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set customerNumber
     *
     * @param string $customerNumber customerNumber
     *
     * @return self
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;

        return $this;
    }

    /**
     * Get customerNumber
     *
     * @return string $customerNumber customerNumber
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * Set salutation
     *
     * @param collection $salutation salutation
     *
     * @return self
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * Get salutation
     *
     * @return collection $salutation salutation
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set firstName
     *
     * @param string $firstName firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string $lastName lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set clientAdvisor
     *
     * @param string $clientAdvisor clientAdvisor
     *
     * @return self
     */
    public function setClientAdvisor($clientAdvisor)
    {
        $this->clientAdvisor = $clientAdvisor;

        return $this;
    }

    /**
     * Get clientAdvisor
     *
     * @return string $clientAdvisor clientAdvisor
     */
    public function getClientAdvisor()
    {
        return $this->clientAdvisor;
    }

    /**
     * Set customerType
     *
     * @param string $customerType customerType
     *
     * @return self
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;

        return $this;
    }

    /**
     * Get customerType
     *
     * @return string $customerType
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * Set domicile
     *
     * @param string $domicile domicile
     *
     * @return self
     */
    public function setDomicile($domicile)
    {
        $this->domicile = $domicile;

        return $this;
    }

    /**
     * Get domicile
     *
     * @return string $domicile domicile
     */
    public function getDomicile()
    {
        return $this->domicile;
    }

    /**
     * Set customerOpeningDate
     *
     * @param date $customerOpeningDate customerOpeningDate
     *
     * @return self
     */
    public function setCustomerOpeningDate($customerOpeningDate)
    {
        $this->customerOpeningDate = $customerOpeningDate;

        return $this;
    }

    /**
     * Get customerOpeningDate
     *
     * @return date $customerOpeningDate customerOpeningDate
     */
    public function getCustomerOpeningDate()
    {
        return $this->customerOpeningDate;
    }

    /**
     * Set nationality
     *
     * @param collection $nationality nationality
     *
     * @return self
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality
     *
     * @return collection $nationality nationality
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set sex
     *
     * @param collection $sex sex
     *
     * @return self
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return collection $sex sex
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set birthDate
     *
     * @param date $birthDate birthDate
     *
     * @return self
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return date $birthDate birthDate
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set bankAccount
     *
     * @param string $bankAccount bankAccount
     *
     * @return self
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return string $bankAccount bankAccount
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set maritalStatus
     *
     * @param collection $maritalStatus maritalStatus
     *
     * @return self
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    /**
     * Get maritalStatus
     *
     * @return collection $maritalStatus maritalStatus
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * Set education
     *
     * @param string $education education
     *
     * @return self
     */
    public function setEducation($education)
    {
        $this->education = $education;

        return $this;
    }

    /**
     * Get education
     *
     * @return string $education education
     */
    public function getEducation()
    {
        return $this->education;
    }

    /**
     * Set employmentStatus
     *
     * @param collection $employmentStatus employmentStatus
     *
     * @return self
     */
    public function setEmploymentStatus($employmentStatus)
    {
        $this->employmentStatus = $employmentStatus;

        return $this;
    }

    /**
     * Get employmentStatus
     *
     * @return collection $employmentStatus employmentStatus
     */
    public function getEmploymentStatus()
    {
        return $this->employmentStatus;
    }

    /**
     * Set profession
     *
     * @param collection $profession profession
     *
     * @return self
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get profession
     *
     * @return collection $profession profession
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set rating
     *
     * @param string $rating rating
     *
     * @return self
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return string $rating rating
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set portfolioValue
     *
     * @param float $portfolioValue portfolioValue
     *
     * @return self
     */
    public function setPortfolioValue($portfolioValue)
    {
        $this->portfolioValue = $portfolioValue;

        return $this;
    }

    /**
     * Get portfolioValue
     *
     * @return float $portfolioValue portfolioValue
     */
    public function getPortfolioValue()
    {
        return $this->portfolioValue;
    }

    /**
     * Set totalAssets
     *
     * @param float $totalAssets totalAssets
     *
     * @return self
     */
    public function setTotalAssets($totalAssets)
    {
        $this->totalAssets = $totalAssets;

        return $this;
    }

    /**
     * Get totalAssets
     *
     * @return float $totalAssets totalAssets
     */
    public function getTotalAssets()
    {
        return $this->totalAssets;
    }

    /**
     * Set strategy
     *
     * @param string $strategy strategy
     *
     * @return self
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Get strategy
     *
     * @return string $strategy strategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Set assetManagementCode
     *
     * @param string $assetManagementCode assetManagementCode
     *
     * @return self
     */
    public function setAssetManagementCode($assetManagementCode)
    {
        $this->assetManagementCode = $assetManagementCode;

        return $this;
    }

    /**
     * Get assetManagementCode
     *
     * @return string $assetManagementCode assetManagementCode
     */
    public function getAssetManagementCode()
    {
        return $this->assetManagementCode;
    }

    /**
     * Set performance
     *
     * @param string $performance performance
     *
     * @return self
     */
    public function setPerformance($performance)
    {
        $this->performance = $performance;

        return $this;
    }

    /**
     * Get performance
     *
     * @return string $performance performance
     */
    public function getPerformance()
    {
        return $this->performance;
    }

    /**
     * Set taxDomicil
     *
     * @param collection $taxDomicil taxDomicil
     *
     * @return self
     */
    public function setTaxDomicil($taxDomicil)
    {
        $this->taxDomicil = $taxDomicil;

        return $this;
    }

    /**
     * Get taxDomicil
     *
     * @return collection $taxDomicil taxDomicil
     */
    public function getTaxDomicil()
    {
        return $this->taxDomicil;
    }

    /**
     * Set qiBasket
     *
     * @param collection $qiBasket qiBasket
     *
     * @return self
     */
    public function setQiBasket($qiBasket)
    {
        $this->qiBasket = $qiBasket;

        return $this;
    }

    /**
     * Get qiBasket
     *
     * @return collection $qiBasket qiBasket
     */
    public function getQiBasket()
    {
        return $this->qiBasket;
    }

    /**
     * Set euTaxLiability
     *
     * @param collection $euTaxLiability euTaxLiability
     *
     * @return self
     */
    public function setEuTaxLiability($euTaxLiability)
    {
        $this->euTaxLiability = $euTaxLiability;

        return $this;
    }

    /**
     * Get euTaxLiability
     *
     * @return collection $euTaxLiability euTaxLiability
     */
    public function getEuTaxLiability()
    {
        return $this->euTaxLiability;
    }

    /**
     * Set mifidCategory
     *
     * @param collection $mifidCategory mifidCategory
     *
     * @return self
     */
    public function setMifidCategory($mifidCategory)
    {
        $this->mifidCategory = $mifidCategory;

        return $this;
    }

    /**
     * Get mifidCategory
     *
     * @return collection $mifidCategory mifidCategory
     */
    public function getMifidCategory()
    {
        return $this->mifidCategory;
    }

    /**
     * Set bVGCode
     *
     * @param boolean $bVGCode bVGCode
     *
     * @return self
     */
    public function setBVGCode($bVGCode)
    {
        $this->BVGCode = $bVGCode;

        return $this;
    }

    /**
     * Get bVGCode
     *
     * @return boolean $bVGCode bVGCode
     */
    public function getBVGCode()
    {
        return $this->BVGCode;
    }

    /**
     * Set customerCategory
     *
     * @param collection $customerCategory customerCategory
     *
     * @return self
     */
    public function setCustomerCategory($customerCategory)
    {
        $this->customerCategory = $customerCategory;

        return $this;
    }

    /**
     * Get customerCategory
     *
     * @return collection $customerCategory customerCategory
     */
    public function getCustomerCategory()
    {
        return $this->customerCategory;
    }

    /**
     * Set mainType
     *
     * @param collection $mainType mainType
     *
     * @return self
     */
    public function setMainType($mainType)
    {
        $this->mainType = $mainType;

        return $this;
    }

    /**
     * Get mainType
     *
     * @return collection $mainType
     */
    public function getMainType()
    {
        return $this->mainType;
    }

    /**
     * Set customerSegment
     *
     * @param collection $customerSegment customerSegment
     *
     * @return self
     */
    public function setCustomerSegment($customerSegment)
    {
        $this->customerSegment = $customerSegment;

        return $this;
    }

    /**
     * Get customerSegment
     *
     * @return collection $customerSegment customerSegment
     */
    public function getCustomerSegment()
    {
        return $this->customerSegment;
    }

    /**
     * Set language
     *
     * @param collection $language language
     *
     * @return self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return collection $language language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set clientSpecificValues
     *
     * @param collection $clientSpecificValues clientSpecificValues
     *
     * @return self
     */
    public function setClientSpecificValues($clientSpecificValues)
    {
        $this->clientSpecificValues = $clientSpecificValues;

        return $this;
    }

    /**
     * Get clientSpecificValues
     *
     * @return collection $clientSpecificValues clientSpecificValues
     */
    public function getClientSpecificValues()
    {
        return $this->clientSpecificValues;
    }

    /**
     * Set documents
     *
     * @param collection $documents documents
     *
     * @return self
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get documents
     *
     * @return collection $documents documents
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set legalAddress
     *
     * @param collection $legalAddress legalAddress
     *
     * @return self
     */
    public function setLegalAddress($legalAddress)
    {
        $this->legalAddress = $legalAddress;

        return $this;
    }

    /**
     * Get legalAddress
     *
     * @return collection $legalAddress legalAddress
     */
    public function getLegalAddress()
    {
        return $this->legalAddress;
    }

    /**
     * Set contacts
     *
     * @param collection $contacts contacts
     *
     * @return self
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get contacts
     *
     * @return collection $contacts contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set relations
     *
     * @param collection $relations relations
     *
     * @return self
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get relations
     *
     * @return collection $relations relations
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
