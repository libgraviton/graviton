<?php
/**
 * doctrine custom type to handle reading and writing $refs attributes
 */

namespace Graviton\DocumentBundle\Types;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
     * @var Router
     */
    var $router;

    /**
     * inject a router
     *
     * This uses setter injection due to the fact that doctrine doesn't do constructor injection
     *
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
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
        // @todo use router to generate an absolute url from $value
    }

    /**
     * return a closure as string that sets $return if field is a regular field
     *
     * @return string
     */
    public function closureToPHP()
    {
        // @todo figure out how to inject a router into this string closure stuff
    }

    /**
     * return the mongodb representation from a php value
     *
     * @param string $value value of reference as URI
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value)
    {
        if (empty($this->touter)) {
            throw new \RuntimeException('no router injected into '.__CLASS__);
        }
        // @todo generate these using an injected router
        $collection = 'foo';
        $id = 1234;
        return \MongoDBRef::create($collection, $id);
    }
}
