<?php
/**
 * dummy document
 */

namespace Graviton\CoreBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Version
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 *
 * @ODM\Document
 */
class Dummy
{

    /**
     * @var string
     *
     * @ODM\Id(type="id", strategy="CUSTOM", options={"class"="Graviton\DocumentBundle\Doctrine\IdGenerator"})
     */
    private $id;

    /**
     * get Id
     *
     * @return mixed Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set Id
     *
     * @param mixed $id id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
