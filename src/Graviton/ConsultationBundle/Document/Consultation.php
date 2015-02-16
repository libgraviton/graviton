<?php
/**
 * consultation document
 */

namespace Graviton\ConsultationBundle\Document;

/**
 * Graviton\ConsultationBundle\Document\Consultation
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Consultation
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var date $creationDate
     */
    protected $creationDate;

    /**
     * @var string $dossierName
     */
    protected $dossierName;

    /**
     * @var string $prettyDossierName
     */
    protected $prettyDossierName;

    /**
     * @var date $lastInterestUpdate
     */
    protected $lastInterestUpdate;

    /**
     * @var hash $customer
     */
    protected $customer;

    /**
     * @var hash $agenda
     */
    protected $agenda;

    /**
     * @var hash $investmentConsultation
     */
    protected $investmentConsultation;

    /**
     * @var hash $investmentStockData
     */
    protected $investmentStockData;

    /**
     * @var hash $modulePDFImageStorage
     */
    protected $modulePDFImageStorage;

    /**
     * @var hash $realEstateStockData
     */
    protected $realEstateStockData;

    /**
     * @var hash $requisitionConsultation
     */
    protected $requisitionConsultation;

    /**
     * @var hash $toolboxNotes
     */
    protected $toolboxNotes;

    /**
     * @var hash $paramterData
     */
    protected $paramterData;

    /**
     * @var hash $realEstateConsultation
     */
    protected $realEstateConsultation;

    /**
     * @var hash $funding
     */
    protected $funding;

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
     * Set creationDate
     *
     * @param date $creationDate Creation date
     *
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return date $creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set dossierName
     *
     * @param string $dossierName Dossier name
     *
     * @return self
     */
    public function setDossierName($dossierName)
    {
        $this->dossierName = $dossierName;

        return $this;
    }

    /**
     * Get dossierName
     *
     * @return string $dossierName Dossier name
     */
    public function getDossierName()
    {
        return $this->dossierName;
    }

    /**
     * Set prettyDossierName
     *
     * @param string $prettyDossierName Pretty dossier name
     *
     * @return self
     */
    public function setPrettyDossierName($prettyDossierName)
    {
        $this->prettyDossierName = $prettyDossierName;

        return $this;
    }

    /**
     * Get prettyDossierName
     *
     * @return string $prettyDossierName Pretty dossier name
     */
    public function getPrettyDossierName()
    {
        return $this->prettyDossierName;
    }

    /**
     * Set lastInterestUpdate
     *
     * @param date $lastInterestUpdate Last interest update
     *
     * @return self
     */
    public function setLastInterestUpdate($lastInterestUpdate)
    {
        $this->lastInterestUpdate = $lastInterestUpdate;

        return $this;
    }

    /**
     * Get lastInterestUpdate
     *
     * @return date $lastInterestUpdate Last interest update
     */
    public function getLastInterestUpdate()
    {
        return $this->lastInterestUpdate;
    }

    /**
     * Set customer
     *
     * @param hash $customer Customer
     *
     * @return self
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return hash $customer Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set agenda
     *
     * @param hash $agenda Agenda
     *
     * @return self
     */
    public function setAgenda($agenda)
    {
        $this->agenda = $agenda;

        return $this;
    }

    /**
     * Get agenda
     *
     * @return hash $agenda Agenda
     */
    public function getAgenda()
    {
        return $this->agenda;
    }

    /**
     * Set investmentConsultation
     *
     * @param hash $investmentConsultation Investment consultation
     *
     * @return self
     */
    public function setInvestmentConsultation($investmentConsultation)
    {
        $this->investmentConsultation = $investmentConsultation;

        return $this;
    }

    /**
     * Get investmentConsultation
     *
     * @return hash $investmentConsultation Investment consultation
     */
    public function getInvestmentConsultation()
    {
        return $this->investmentConsultation;
    }

    /**
     * Set investmentStockData
     *
     * @param hash $investmentStockData Investment stock data
     *
     * @return self
     */
    public function setInvestmentStockData($investmentStockData)
    {
        $this->investmentStockData = $investmentStockData;

        return $this;
    }

    /**
     * Get investmentStockData
     *
     * @return hash $investmentStockData Investment stock data
     */
    public function getInvestmentStockData()
    {
        return $this->investmentStockData;
    }

    /**
     * Set modulePDFImageStorage
     *
     * @param hash $modulePDFImageStorage PDF image store
     *
     * @return self
     */
    public function setModulePDFImageStorage($modulePDFImageStorage)
    {
        $this->modulePDFImageStorage = $modulePDFImageStorage;

        return $this;
    }

    /**
     * Get modulePDFImageStorage PDF image store
     *
     * @return hash $modulePDFImageStorage
     */
    public function getModulePDFImageStorage()
    {
        return $this->modulePDFImageStorage;
    }

    /**
     * Set realEstateStockData
     *
     * @param hash $realEstateStockData Real estate stock data
     *
     * @return self
     */
    public function setRealEstateStockData($realEstateStockData)
    {
        $this->realEstateStockData = $realEstateStockData;

        return $this;
    }

    /**
     * Get realEstateStockData
     *
     * @return hash $realEstateStockData Real estate stock data
     */
    public function getRealEstateStockData()
    {
        return $this->realEstateStockData;
    }

    /**
     * Set requisitionConsultation
     *
     * @param hash $requisitionConsultation Requisition consultation
     *
     * @return self
     */
    public function setRequisitionConsultation($requisitionConsultation)
    {
        $this->requisitionConsultation = $requisitionConsultation;

        return $this;
    }

    /**
     * Get requisitionConsultation
     *
     * @return hash $requisitionConsultation Requisition consultation
     */
    public function getRequisitionConsultation()
    {
        return $this->requisitionConsultation;
    }

    /**
     * Set toolboxNotes
     *
     * @param hash $toolboxNotes Toolbox notes
     *
     * @return self
     */
    public function setToolboxNotes($toolboxNotes)
    {
        $this->toolboxNotes = $toolboxNotes;

        return $this;
    }

    /**
     * Get toolboxNotes
     *
     * @return hash $toolboxNotes Toolbox notes
     */
    public function getToolboxNotes()
    {
        return $this->toolboxNotes;
    }

    /**
     * Set paramterData
     *
     * @param hash $paramterData Parameter data
     *
     * @return self
     */
    public function setParamterData($paramterData)
    {
        $this->paramterData = $paramterData;

        return $this;
    }

    /**
     * Get paramterData
     *
     * @return hash $paramterData Parameter data
     */
    public function getParamterData()
    {
        return $this->paramterData;
    }

    /**
     * Set realEstateConsultation
     *
     * @param hash $realEstateConsultation Real estate consultation
     *
     * @return self
     */
    public function setRealEstateConsultation($realEstateConsultation)
    {
        $this->realEstateConsultation = $realEstateConsultation;

        return $this;
    }

    /**
     * Get realEstateConsultation
     *
     * @return hash $realEstateConsultation Real estate consultation
     */
    public function getRealEstateConsultation()
    {
        return $this->realEstateConsultation;
    }

    /**
     * Set funding
     *
     * @param hash $funding Funding
     *
     * @return self
     */
    public function setFunding($funding)
    {
        $this->funding = $funding;

        return $this;
    }

    /**
     * Get funding
     *
     * @return hash $funding Funding
     */
    public function getFunding()
    {
        return $this->funding;
    }
}
