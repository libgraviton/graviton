<?php
/**
 * Document for representing Products.
 */

namespace Graviton\CoreBundle\Document;

/**
 * Product
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Product
{
    /**
     * @var string app id
     */
    protected $id;

    /**
     * @var string[] app title in multiple languages
     */
    protected $name;

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string[] $name
     */
    public function getName()
    {
        return $this->name;
    }
}
