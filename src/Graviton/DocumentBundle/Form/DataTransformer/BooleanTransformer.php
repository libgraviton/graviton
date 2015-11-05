<?php
/**
 * transformer for converting submitted data into booleans
 */

namespace Graviton\DocumentBundle\Form\DataTransformer;

use Gedmo\Exception\RuntimeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * transformer for converting submitted data into booleans
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class BooleanTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var bool|array
     */
    private $submittedData;

    /**
     * set the property path of the field
     *
     * @param PropertyPathInterface $propertyPath property path
     * @return void
     */
    public function setPropertyPath(PropertyPathInterface $propertyPath)
    {
        $this->propertyPath = (string) $propertyPath;
    }

    /**
     * set the original submitted data
     *
     * @param bool|array $submittedData the original submitted data
     * @return void
     */
    public function setSubmittedData($submittedData)
    {
        $this->submittedData = $submittedData;
    }

    /**
     * @inheritDoc
     *
     * @param mixed $value The value in the original representation
     * @return mixed The transformed value
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     *
     * @param mixed $value The value in the transformed representation
     * @return mixed The original value
     */
    public function reverseTransform($value)
    {
        if (!isset($this->propertyPath)) {
            throw new RuntimeException('propertyPath must be set');
        }

        $originData = $this->submittedData;
        if (is_array($this->submittedData) && preg_match('@^\[+[0-9]\]$@', $this->propertyPath)) {
            $index = (int) trim($this->propertyPath, '[]');
            if (array_key_exists($index, $this->submittedData)) {
                $originData = $this->submittedData[$index];
            }
        }
        // the submit method of the form convert false to null and true to '1'
        if (is_bool($originData) && ($value === null || $value === '1')) {
            $value = $originData;
        } elseif ($value === null) {
            $value = '';
        }

        return $value;
    }
}
