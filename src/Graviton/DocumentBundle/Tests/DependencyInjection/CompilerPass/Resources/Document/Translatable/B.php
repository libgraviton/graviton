<?php
/**
 * test dummy document
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Translatable;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
#[ODM\Document]
class B
{
    #[ODM\Id(type: "id", options: ["class" => "Graviton\DocumentBundle\Doctrine\IdGenerator"], strategy: "CUSTOM")]
    protected $id;

    #[ODM\Field(type: "string")]
    protected $key;

    #[ODM\Field(type: "translatable")]
    protected $title;

    #[ODM\EmbedOne(targetDocument: C::class)]
    protected $bchild;

    #[ODM\EmbedMany(strategy: "setArray", targetDocument: C::class)]
    protected $bchildren;
}
