<?php
/**
 * Test class file
 */

namespace Graviton\GeneratorBundle\Tests\Serializer\Handler;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TestData
{
    /**
     * @var \ArrayObject
     */
    private $a;
    /**
     * @var \ArrayObject[]
     */
    private $b;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->a = new \ArrayObject();
        $this->b = [];
    }

    /**
     * @return \ArrayObject
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @param \ArrayObject $a A
     * @return $this
     */
    public function setA(\ArrayObject $a)
    {
        $this->a = $a;
        return $this;
    }

    /**
     * @return \ArrayObject[]
     */
    public function getB()
    {
        return $this->b;
    }

    /**
     * @param \ArrayObject[] $b B
     * @return $this
     */
    public function setB(array $b)
    {
        $this->b = $b;
        return $this;
    }
}
