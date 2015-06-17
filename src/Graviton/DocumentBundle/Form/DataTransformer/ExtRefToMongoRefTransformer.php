<?php
/**
 * transform an extref to its internal mongdbref representation
 */

namespace Graviton\DocumentBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefToMongoRefTransformer implements DataTransformerInterface
{
    /**
     * transform a MongRef to a URL
     *
     * @param MongoDBRef|null $ref mongoref from database
     *
     * @return string
     */
    public function transform($ref)
    {
    }

    /**
     * transform an URL to a MongoRef
     *
     * @param  string $url URL from client
     *
     * @return MongoDBRef|null
     */
    public function reverseTransform($url)
    {
    }
}
