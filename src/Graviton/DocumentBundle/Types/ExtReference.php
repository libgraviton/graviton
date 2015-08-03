<?php
/**
 * doctrine custom type to handle reading and writing $refs attributes
 */

namespace Graviton\DocumentBundle\Types;

use Graviton\DocumentBundle\Service\ExtReferenceResolverInterface;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * based on http://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/basic-mapping.html#custom-mapping-types
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReference extends Type
{
    /**
     * @var ExtReferenceResolverInterface
     */
    private $resolver;

    /**
     * inject a resolver
     *
     * This uses setter injection due to the fact that doctrine doesn't do constructor injection
     *
     * @param ExtReferenceResolverInterface $resolver resolver
     *
     * @return void
     */
    public function setResolver(ExtReferenceResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * get php value when field is used as identifier
     *
     * @param \MongoDBRef $value ref from mongodb
     *
     * @return string
     */
    public function convertToPHPValue($value)
    {
        try {
            return $this->resolver->getUrl($value);
        } catch (\InvalidArgumentException $e) {
            return '';
        }
    }

    /**
     * return a closure as string that sets $return if field is a regular field
     *
     * @return string
     */
    public function closureToPHP()
    {
        // return full value for later processing since we do not have mappings during hydrator generation
        return '$return = json_encode($value);';
    }

    /**
     * return the mongodb representation from a php value
     *
     * @param string $value value of reference as URI
     *
     * @return array
     */
    public function convertToDatabaseValue($value)
    {
        try {
            return $this->resolver->getDbValue($value);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(
                sprintf('Could not read URL %s', $value),
                0,
                $e
            );
        }
    }
}
