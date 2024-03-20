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
class C
{
    #[ODM\Id(type: "id", options: ["class" => "Graviton\DocumentBundle\Doctrine\IdGenerator"], strategy: "CUSTOM")]
    protected $id;

    #[ODM\Field(type: "string")]
    protected $field;
}
