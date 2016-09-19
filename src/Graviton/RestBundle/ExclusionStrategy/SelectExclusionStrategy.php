<?php
/**
 * Class for exclusion strategies.
 */
namespace Graviton\RestBundle\ExclusionStrategy;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Context;

/**
 * In this Strategy we skip all properties on first level who are not selected if there is a select in rql.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SelectExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @InheritDoc: Whether the class should be skipped.
     * @param ClassMetadata $metadata         blabla
     * @param Context       $navigatorContext blabla
     * @return boolean
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $navigatorContext)
    {
        return false;
    }

    /**
     * @InheritDoc: Whether the property should be skipped.
     * Skipping properties on first level who are not selected if there is a select in rql.
     * @param PropertyMetadata $property blabla
     * @param Context          $context  blabla
     * @return boolean
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        // we are only dealing with the first level of the JSON here
        if ($context->getDepth() > 1) {
            return false;
        }

        $select = false;
        $selectVars=[];
        foreach ($_GET as $getVar => $val) {
            if (strstr($getVar, 'select(')) {
                preg_match('/\((.*?)\)/', $getVar, $match);
                $select = true;
                $selectVars = explode(',', $match[1]);
                break;
            }
        }
        if ($select && ! in_array($property->name, $selectVars)) {
            return true;
        } else {
            return false;
        }
    }
}
