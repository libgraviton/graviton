<?php

namespace Graviton\I18nBundle\Document;

/**
 * Graviton\I18nBundle\Document\Translatable
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class Translatable
{
    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var string $domain
     */
    protected $domain;

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $original
     */
    protected $original;

    /**
     * @var string $translated
     */
    protected $translated;

    /**
     * @var boolean $isLocalized
     */
    protected $isLocalized;

    /**
     * set id
     *
     * @param string $id id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set domain
     *
     * @param string $domain domain
     *
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * set locale
     *
     * @param string $locale locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * get original
     *
     * @param string $original original string
     *
     * @return void
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * get original
     *
     * @return string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * set translated
     *
     * @param string $translated translated string
     *
     * @return void
     */
    public function setTranslated($translated)
    {
        $this->translated = $translated;
    }

    /**
     * get translated string
     *
     * @return string
     */
    public function getTranslated()
    {
        return $this->translated;
    }

    /**
     * set isLocalized
     *
     * @param boolean $isLocalized true/false
     *
     * @return void
     */
    public function setIsLocalized($isLocalized)
    {
        $this->isLocalized = $isLocalized;
    }

    /**
     * is this document localized
     *
     * @return boolean
     */
    public function isLocalized()
    {
        return $this->isLocalized;
    }
}
