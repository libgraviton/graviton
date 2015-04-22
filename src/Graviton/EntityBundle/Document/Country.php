<?php
/**
 * document for representing a country
 */

namespace Graviton\EntityBundle\Document;

/**
 * document for representing a country
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set isoCode
     *
     * @param string $isoCode
     * @return self
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
        return $this;
    }

    /**
     * Set capitalCity
     *
     * @param string $capitalCity
     * @return self
     */
    public function setCapitalCity($capitalCity)
    {
        $this->capitalCity = $capitalCity;
        return $this;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }
}
