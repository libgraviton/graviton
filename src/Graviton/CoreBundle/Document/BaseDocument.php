<?php
/**
 * Document meant to keep unified Base Document structure.
 */

namespace Graviton\CoreBundle\Document;

// To not show in any Serialised output.
use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * App
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 *
 * @ExclusionPolicy("all")
 */
class BaseDocument
{
    /**
     * @var string app id
     */
    protected $iddoc;

    /**
     * @var \datetime modifiedAt date
     */
    protected $modifiedAt;

    /**
     * @var string updatedAt username
     */
    protected $modifiedBy;

    /**
     * @return string
     */
    public function getIddoc()
    {
        return $this->iddoc;
    }

    /**
     * @param string $id DocumentId
     * @return void
     */
    public function setIddoc($id)
    {
        $this->iddoc = $id;
    }

    /**
     * @return \datetime Time it was modified
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param \datetime $modifiedAt When document was modified
     * @return void
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return string
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param string $modifiedBy Who modified document
     * @return void
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }
}
