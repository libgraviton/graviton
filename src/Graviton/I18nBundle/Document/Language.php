<?php

namespace Graviton\I18nBundle\Document;



/**
 * Graviton\I18nBundle\Document\Language
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class Language
{
    /**
     * @var MongoId $tag
     */
    protected $tag;


    /**
     * Get tag
     *
     * @return string $tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set tag
     *
     * @param string $tag language tag value
     *
     * @return void
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}
