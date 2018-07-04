<?php
/**
 * get a repository instance for a given class
 */

namespace Graviton\SchemaBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RepositoryFactory
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry registry used to fetch a repository
     */
    public function __construct(
        ManagerRegistry $managerRegistry
    ) {
        $this->managerRegistry = $managerRegistry;
    }
    /**
     * get a repository class for a given class name
     *
     * @param string $documentId class to instanciate
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function get($documentId)
    {
        return $this->managerRegistry
            ->getRepository($documentId);
    }
}
