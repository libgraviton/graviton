<?php
/**
 * whoami restriction handler
 */
namespace Graviton\RestBundle\Restriction\Handler;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RqlParser\Node\AbstractQueryNode;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Graviton\SecurityBundle\Service\SecurityUtils;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class Whoami implements HandlerInterface
{

    /**
     * @var SecurityUtils
     */
    private $securityUtils;

    /**
     * Whoami constructor.
     *
     * @param SecurityUtils $securityUtils security utils
     */
    public function __construct(SecurityUtils $securityUtils)
    {
        $this->securityUtils = $securityUtils;
    }

    /**
     * returns the name of this handler, string based id. this is referenced in the service definition.
     *
     * @return string handler name
     */
    public function getHandlerName()
    {
        return 'whoami';
    }

    /**
     * gets the actual value or an AbstractQueryNode that is used to filter the data.
     *
     * @param DocumentRepository $repository the repository
     * @param string             $fieldPath  field path
     *
     * @return string|AbstractQueryNode string for eq() filtering or a AbstractQueryNode instance
     */
    public function getValue(DocumentRepository $repository, $fieldPath)
    {
        $user = 'anonymous';

        $securityUser = $this->securityUtils->getSecurityUser();
        if ($securityUser instanceof SecurityUser) {
            $user = trim(strtolower($securityUser->getUsername()));
        }

        return $user;
    }
}
