<?php
/**
 * Document class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Document
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Document
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var AbstractField[]
     */
    private $fields = [];

    /**
     * Constructor
     *
     * @param string $className Class name
     * @param array  $fields    Fields
     */
    public function __construct($className, array $fields)
    {
        $this->className = $className;
        $this->fields = $fields;
    }

    /**
     * Get class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->className;
    }

    /**
     * Get fields
     *
     * @return AbstractField[]
     */
    public function getFields()
    {
        return $this->fields;
    }
}
