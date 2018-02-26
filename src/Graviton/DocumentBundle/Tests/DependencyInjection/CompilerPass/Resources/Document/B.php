<?php
/**
 * test dummy document
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class B implements TranslatableDocumentInterface
{

    /**
     * return all translatable fields
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array('title');
    }

    /**
     * return all pretranslated fields
     *
     * @return string[]
     */
    public function getPreTranslatedFields()
    {
        return array();
    }
}
