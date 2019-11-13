<?php
/**
 * test dummy document
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 *
 * @ODM\Document
 */
class A
{

    /**
     * @ODM\Id(type="string", strategy="CUSTOM", options={"class"="Graviton\DocumentBundle\Doctrine\IdGenerator"})
     */
    protected $id;

    /**
     * @ODM\Field(type="int")
     */
    protected $integer;

    /**
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * @ODM\Field(type="extref")
     */
    protected $extref;

    /**
     * @ODM\Field(type="boolean")
     */
    protected $boolean;

    /**
     * @ODM\Field(type="date")
     */
    protected $datetime;

    /**
     * @ODM\Field(type="float")
     */
    protected $float;

    /**
     * @ODM\Field(type="hash")
     */
    protected $unstruct;

    /**
     * @ODM\EmbedOne(targetDocument="Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\B")
     */
    protected $achild;

    /**
     * @ODM\EmbedMany(targetDocument="Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\B", strategy="setArray")
     */
    protected $achildren;

}
