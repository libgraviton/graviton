<?php
/**
 * security AnonymousUser entity
 * A basic user to allow loggin, query and find object based on anonymous authentication.
 */

namespace Graviton\SecurityBundle\Entities;

/**
 * Class AnonymousUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnonymousUser
{
    const DEFAULT_ID = 0;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username = 'anonymous';

    /**
     * Constructor of the class.
     */
    public function __construct()
    {
        $this->setId(self::DEFAULT_ID);
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param int $id id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
