<?php
/**
 * Field class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Document field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Field extends AbstractField
{
    /**
     * @var string
     */
    private $type;

    /**
     * Constructor
     *
     * @param string $type                  Field type
     * @param string $fieldName             Field name
     * @param string $exposedName           Exposed name
     * @param bool   $readOnly              Read only
     * @param bool   $required              Is required
     * @param bool   $searchable            Is searchable
     * @param bool   $recordOriginException Is an exception to record origin
     */
    public function __construct(
        $type,
        $fieldName,
        $exposedName,
        $readOnly,
        $required,
        $searchable,
        $recordOriginException
    ) {
        $this->type = $type;
        parent::__construct($fieldName, $exposedName, $readOnly, $required, $searchable, $recordOriginException);
    }

    /**
     * Get field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
