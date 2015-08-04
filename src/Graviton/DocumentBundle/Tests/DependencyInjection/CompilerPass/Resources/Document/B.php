<?php

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

class B implements TranslatableDocumentInterface {

    /**
     * return all translatable fields
     *
     * @return string[]
     */
    public function getTranslatableFields() {
        return array('title');
    }

    /**
     * return all pretranslated fields
     *
     * @return string[]
     */
    public function getPreTranslatedFields() {
        return array();
    }
}
