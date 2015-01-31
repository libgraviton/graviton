<?php

namespace Graviton\I18nBundle\Document;

/**
 * A translatable document
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
interface TranslatableDocumentInterface
{
    /**
     * return all translatable fields
     *
     * @return string[]
     */
    public function getTranslatableFields();
}
