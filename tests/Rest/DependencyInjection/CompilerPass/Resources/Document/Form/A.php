<?php
/**
 * test dummy document
 */

namespace Graviton\Tests\Rest\DependencyInjection\CompilerPass\Resources\Document\Form;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
#[ODM\Document]
class A
{

    #[ODM\Id(type: "id", options: ["class" => "Graviton\DocumentBundle\Doctrine\IdGenerator"], strategy: "CUSTOM")]
    protected $id;

    #[ODM\Field(type: "int")]
    protected $integer;

    #[ODM\Field(type: "string")]
    protected $title;

    #[ODM\Field(type: "extref")]
    protected $extref;

    #[ODM\Field(type: "boolean")]
    protected $boolean;

    #[ODM\Field(type: "date")]
    protected $datetime;

    #[ODM\Field(type: "float")]
    protected $float;

    #[ODM\Field(type: "hash")]
    protected $unstruct;

    #[ODM\EmbedOne(targetDocument: B::class)]
    protected $achild;

    #[ODM\EmbedMany(strategy: "setArray", targetDocument: B::class)]
    protected $achildren;
}
