<?php

namespace Graviton\EntityBundle\Document;

/**
 * document for representing a country
 *
 * @category GravitonEntityBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class Country
{
    /**
     * @var string document/country id
     */
    protected $id;

    /**
     * @var string Country Name
     */
    protected $name;

    /**
     * @var string ISO country code
     */
    protected $isoCode;

    /**
     * @var string capital city of country
     */
    protected $capitalCity;

    /**
     * @var string Longitude of country
     */
    protected $longitude;

    /**
     * @var string Latitude of country
     */
    protected $latitude;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * get ISO code of country
     *
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * get name of capital city
     *
     * @return string
     */
    public function getCapitalCity()
    {
        return $this->capitalCity;
    }

    /**
     * get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
}
