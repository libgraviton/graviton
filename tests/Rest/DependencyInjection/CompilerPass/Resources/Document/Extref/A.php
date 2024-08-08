<?php
/**
 * test dummy document
 */

namespace Graviton\Tests\Rest\DependencyInjection\CompilerPass\Resources\Document\Extref;

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

    #[ODM\Field(type: "string")]
    protected $key;

    #[ODM\Field(type: "extref")]
    protected $ref;

    #[ODM\EmbedOne(targetDocument: B::class)]
    protected $achild;

    #[ODM\EmbedMany(strategy: "setArray", targetDocument: B::class)]
    protected $achildren;
}
