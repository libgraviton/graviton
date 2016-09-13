<?php
/**
 * To manage the data to be saved into DB as last thing to do.
 */
namespace Graviton\AuditTrackingBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\AuditTrackingBundle\Document\AuditTracking;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class StoreManager
 * @package Graviton\AuditTrackingBundle\Manager
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class StoreManager
{
    const AUDIT_HEADER_KEY = 'x-header-audit-thread';

    /** @var ActivityManager */
    private $activityManager;

    /** @var DocumentManager */
    private $documentManager;
    
    /** @var SecurityUser */
    private $securityUser;

    /**
     * StoreManager constructor.
     * @param ActivityManager $activityManager Main activity manager
     * @param ManagerRegistry $doctrine        Doctrine document mapper
     * @param TokenStorage    $tokenStorage    Sf Auth token storage
     */
    public function __construct(
        ActivityManager $activityManager,
        ManagerRegistry $doctrine,
        TokenStorage $tokenStorage
    ) {
        $this->activityManager = $activityManager;
        $this->documentManager = $doctrine->getManager();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Save data to DB
     * onKernelResponse
     *
     * @param FilterResponseEvent $event Sf fired kernel event
     *
     * @return void
     */
    public function persistEvents(FilterResponseEvent $event)
    {
        if (!($events = $this->activityManager->getEvents())
            || !($username = $this->getSecurityUsername())) {
            return;
        }

        $thread = $this->generateUUID();
        $response = $event->getResponse();
        
        // If request is valid we save it or we do not.
        if (!$this->activityManager->getConfigValue('log_on_failure', 'bool')) {
            if (!$response->isSuccessful()) {
                // TODO log that we do not save
                return;
            }
        }

        // Set Audit header information
        $response->headers->set(self::AUDIT_HEADER_KEY, $thread);

        foreach ($events as $event) {
            $this->trackEvent($event, $thread, $username);
        }
    }

    /**
     * Save the event to DB
     *
     * @param AuditTracking $event    Performed by user
     * @param string        $thread   The thread ID
     * @param string        $username User connected name
     * @return void
     */
    private function trackEvent($event, $thread, $username)
    {
        // Request information
        $event->setThread($thread);
        $event->setUsername($username);

        try {
            $this->documentManager->persist($event);
            $this->documentManager->flush($event);
        } catch (\Exception $e) {
            // TODO LOG the error and event
        }
    }



    /**
     * Generate a unique identifer
     *
     * @return string
     */
    private function generateUUID()
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            return uniqid('unq', true);
        }

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Find current user
     *
     * @return string|bool
     */
    private function getSecurityUser()
    {
        /** @var PreAuthenticatedToken $token */
        if (($token = $this->tokenStorage->getToken())
            && ($user = $token->getUser()) instanceof UserInterface ) {
            return $user;
        }
        return false;
    }

    /**
     * Last check before saving the Event into DB
     *
     * @return bool|string
     */
    public function getSecurityUsername()
    {
        // No securityUser, no tracking
        if (!($this->securityUser = $this->getSecurityUser())) {
            return false;
        }

        // Check if we wanna log test and localhost calls
        if (!$this->activityManager->getConfigValue('log_test_calls', 'bool')) {
            if (!$this->securityUser->hasRole(SecurityUser::ROLE_CONSULTANT)) {
                return false;
            }
        }

        return $this->securityUser->getUsername();
    }
}
