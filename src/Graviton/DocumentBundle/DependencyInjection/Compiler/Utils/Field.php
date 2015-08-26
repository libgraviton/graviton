<?php
/**
 * Field class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Document field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * @param string $type        Field type
     * @param string $fieldName   Field name
     * @param string $exposedName Exposed name
     * @param bool   $readOnly    Read only
     */
    public function __construct($type, $fieldName, $exposedName, $readOnly)
    {
        $this->type = $type;
        parent::__construct($fieldName, $exposedName, $readOnly);
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
