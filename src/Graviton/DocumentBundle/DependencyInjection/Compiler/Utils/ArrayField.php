<?php
/**
 * ArrayField class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Document array field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ArrayField extends AbstractField
{
    /**
     * @var string
     */
    private $serializerType;

    /**
     * Constructor
     *
     * @param string $serializerType Field type
     * @param string $fieldName      Field name
     * @param string $exposedName    Exposed name
     * @param bool   $readOnly       Read only
     * @param bool   $required       Is required
     */
    public function __construct($serializerType, $fieldName, $exposedName, $readOnly, $required)
    {
        $this->serializerType = $serializerType;
        parent::__construct($fieldName, $exposedName, $readOnly, $required, false);
    }

    /**
     * Get item type
     *
     * @return string
     */
    public function getItemType()
    {
        if (!preg_match('/array\<(.+)\>/i', $this->serializerType, $matches)) {
            return $this->serializerType;
        }

        $map = [
            'DateTime'  => 'date',
            'integer'   => 'int',
            'float'     => 'float',
            'double'    => 'float',
            'boolean'   => 'boolean',
            'extref'    => 'extref',
        ];
        return isset($map[$matches[1]]) ? $map[$matches[1]] : $matches[1];
    }
}
