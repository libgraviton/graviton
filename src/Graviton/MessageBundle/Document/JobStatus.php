<?php

/**
 * Document holding a jobs status.
 */

namespace Graviton\MessageBundle\Document;

/**
 * Document holding a jobs status.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JobStatus
{

    /**
     * @var id
     */
    protected $id;

    /**
     * @return mixed The id
     */
    public function getId()
    {
        return $this->id;
    }
}
