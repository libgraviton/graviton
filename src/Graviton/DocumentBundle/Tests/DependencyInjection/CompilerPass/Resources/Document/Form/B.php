<?php
/**
 * test dummy document
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Form;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 *
 * @ODM\Document
 */
class B
{
    /**
     * @ODM\Id(type="string", strategy="CUSTOM", options={"class"="Graviton\DocumentBundle\Doctrine\IdGenerator"})
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $field;

    /**
     * @ODM\EmbedOne(targetDocument="Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Form\C")
     */
    protected $bchild;

    /**
     * @ODM\EmbedMany(targetDocument="Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Form\C", strategy="setArray")
     */
    protected $bchildren;
}
