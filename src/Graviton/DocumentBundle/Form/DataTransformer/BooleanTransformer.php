<?php
/**
 * Created by PhpStorm.
 * User: samuel
 * Date: 21.10.15
 * Time: 10:29
 */

namespace Graviton\DocumentBundle\Form\DataTransformer;


use Gedmo\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

class BooleanTransformer implements DataTransformerInterface
{
    /**
     * @var array
     */
    private $content;

    /**
     * @var PropertyPath
     */
    private $propertyPath;

    /**
     * @inheritDoc
     */
    function __construct(Request $request)
    {
        $this->content = json_decode($request->getContent(), true);
    }


    public function setPropertyPath($propertyPath)
    {
        $this->propertyPath = $propertyPath;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (!isset($this->propertyPath)) {
            throw new RuntimeException("propertyPath must be set");
        }
        // the submit method convert false to null and
        // true to '1'
        if ($value === null || $value === "1") {
            $value = $this->readOriginData();
        }

        return $value;
    }

    /**
     * read the origin data from request
     *
     * @return boolean|string
     */
    private function readOriginData() {
        $properties = explode('.', $this->propertyPath);
        $content = $this->content;
        foreach ($properties as $property) {
            // property was not submitted
            if (!isset($content[$property])) {
                break;
            }
            $content = $content[$property];
            if (is_bool($content) || $content === "1") {
                return $content;
            }
        }
    }
}