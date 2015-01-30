<?php

namespace Graviton\I18nBundle\Document;

/**
 * A translatable document
 *
 * @category I18nBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
interface TranslatableDocumentInterface
{
    /**
     * return all translatable fields
     *
     * @return string[]
     */
    public function getTranslatableFields();
}
