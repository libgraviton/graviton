<?php

namespace Graviton\ConsultationBundle\Document;

/**
 * Graviton\ConsultationBundle\Document\Consultation
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
     * @param  date $creationDate
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
     * @param  string $dossierName
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
     * @return string $dossierName
     */
    public function getDossierName()
    {
        return $this->dossierName;
    }

    /**
     * Set prettyDossierName
     *
     * @param  string $prettyDossierName
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
     * @return string $prettyDossierName
     */
    public function getPrettyDossierName()
    {
        return $this->prettyDossierName;
    }

    /**
     * Set lastInterestUpdate
     *
     * @param  date $lastInterestUpdate
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
     * @return date $lastInterestUpdate
     */
    public function getLastInterestUpdate()
    {
        return $this->lastInterestUpdate;
    }

    /**
     * Set customer
     *
     * @param  hash $customer
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
     * @return hash $customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set agenda
     *
     * @param  hash $agenda
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
     * @return hash $agenda
     */
    public function getAgenda()
    {
        return $this->agenda;
    }

    /**
     * Set investmentConsultation
     *
     * @param  hash $investmentConsultation
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
     * @return hash $investmentConsultation
     */
    public function getInvestmentConsultation()
    {
        return $this->investmentConsultation;
    }

    /**
     * Set investmentStockData
     *
     * @param  hash $investmentStockData
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
     * @return hash $investmentStockData
     */
    public function getInvestmentStockData()
    {
        return $this->investmentStockData;
    }

    /**
     * Set modulePDFImageStorage
     *
     * @param  hash $modulePDFImageStorage
     * @return self
     */
    public function setModulePDFImageStorage($modulePDFImageStorage)
    {
        $this->modulePDFImageStorage = $modulePDFImageStorage;

        return $this;
    }

    /**
     * Get modulePDFImageStorage
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
     * @param  hash $realEstateStockData
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
     * @return hash $realEstateStockData
     */
    public function getRealEstateStockData()
    {
        return $this->realEstateStockData;
    }

    /**
     * Set requisitionConsultation
     *
     * @param  hash $requisitionConsultation
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
     * @return hash $requisitionConsultation
     */
    public function getRequisitionConsultation()
    {
        return $this->requisitionConsultation;
    }

    /**
     * Set toolboxNotes
     *
     * @param  hash $toolboxNotes
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
     * @return hash $toolboxNotes
     */
    public function getToolboxNotes()
    {
        return $this->toolboxNotes;
    }

    /**
     * Set paramterData
     *
     * @param  hash $paramterData
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
     * @return hash $paramterData
     */
    public function getParamterData()
    {
        return $this->paramterData;
    }

    /**
     * Set realEstateConsultation
     *
     * @param  hash $realEstateConsultation
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
     * @return hash $realEstateConsultation
     */
    public function getRealEstateConsultation()
    {
        return $this->realEstateConsultation;
    }

    /**
     * Set funding
     *
     * @param  hash $funding
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
     * @return hash $funding
     */
    public function getFunding()
    {
        return $this->funding;
    }
}
