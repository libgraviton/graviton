<?php
/**
 * AbstractField class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

use Symfony\Component\Form\FormConfigBuilder;

/**
 * Base document field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AbstractField
{
    /**
     * @var string
     */
    private $fieldName;
    /**
     * @var string
     */
    private $exposedName;
    /**
     * @var bool
     */
    private $readOnly;

    /**
     * Constructor
     *
     * @param string $fieldName   Field name
     * @param string $exposedName Exposed name
     * @param bool   $readOnly    Read only
     */
    public function __construct($fieldName, $exposedName, $readOnly)
    {
        $this->fieldName = $fieldName;
        $this->exposedName = $exposedName;
        $this->readOnly = $readOnly;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get exposed name
     *
     * @return string
     */
    public function getExposedName()
    {
        return $this->exposedName;
    }

    /**
     * Is read only
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getFormName()
    {
        if (FormConfigBuilder::isValidName($this->exposedName)) {
            return $this->exposedName;
        }

        $name = $this->exposedName;
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
        $name = preg_replace('/[^a-zA-Z0-9_\-:]/', '', $name);

        return $name === '' ? $this->fieldName : $name;
    }
}
