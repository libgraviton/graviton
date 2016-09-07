<?php
/**
 * Document Listener, to keep track of when and who updated a Document, beyond the logs.
 * RQL enabled search by but fields are by default hidden.
 */

namespace Graviton\CoreBundle\Listener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\CoreBundle\Document\BaseDocument;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Use doctrine odm lister to set or update modified by who and when
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class BaseDocumentListener
{
    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var string for current username
     */
    private $currentUsername;

    /**
     * BaseDocumentListener constructor.
     *
     * @param TokenStorage $tokenStorage Sf session storage
     */
    public function __construct(
        TokenStorage $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Before persisting a change in DB we set who and when did it
     *
     * @param LifecycleEventArgs $args Lifecycle events
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof BaseDocument) {
            $document->setModifiedAt(new \DateTime());
            if ($name = $this->getSecurityUserUsername()) {
                $document->setModifiedBy($name);
            }
        }
    }

    /**
     * Before updating a change in DB we set who and when did it
     *
     * @param LifecycleEventArgs $args Lifecycle events
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof BaseDocument) {
            $document->setModifiedAt(new \DateTime());
            if ($name = $this->getSecurityUserUsername()) {
                $document->setModifiedBy($name);
            }
        }
    }
    
    /**
     * Security needs to be enabled to get Object.
     *
     * @return String Username if available
     */
    public function getSecurityUserUsername()
    {
        if ($this->currentUsername) {
            return $this->currentUsername;
        }

        /** @var PreAuthenticatedToken $token */
        if (($token = $this->tokenStorage->getToken())
            && ($user = $token->getUser()) instanceof UserInterface ) {
            /** @var SecurityUser $user */
            return $user->getUsername();
        }
        return 'none';
    }
}
