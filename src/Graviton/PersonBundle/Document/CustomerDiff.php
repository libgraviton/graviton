<?php
/**
 * document for storing customer diff records
 *
 * @see Graviton\PersonBundle\Controller\AbstractCustomerController for mor details
 */

namespace Graviton\PersonBundle\Document;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CustomerDiff
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var datetime $date
     */
    protected $date;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var object $original
     */
    protected $original;

    /**
     * @var object $new
     */
    protected $new;


    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param DateTime $date date that diff was created
     * @return self
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return datetime $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set type
     *
     * @param string $type type of diff (ie. prospect, ...)
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set original
     *
     * @param object $original original version of object
     * @return self
     */
    public function setOriginal($original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     * Get original
     *
     * @return object $original
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Set new
     *
     * @param object $new new version of object
     * @return self
     */
    public function setNew($new)
    {
        $this->new = $new;
        return $this;
    }

    /**
     * Get new
     *
     * @return object $new
     */
    public function getNew()
    {
        return $this->new;
    }
}
