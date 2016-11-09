<?php
/**
 * security SubnetUser entity
 * A basic user to allow login, query and find object based on anonymous authentication.
 */

namespace Graviton\SecurityBundle\Entities;

/**
 * Class SubnetUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SubnetUser
{
    const DEFAULT_ID = 0;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * Constructor of the class.
     *
     * @param string $username Name of the user
     */
    public function __construct($username)
    {
        $this->username = $username;
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
