<?php
/**
 * RestServiceLocator
 */

namespace Graviton\RestBundle\Service;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Graviton\RestBundle\Model\DocumentModel;
use Psr\Container\ContainerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestServiceLocator
{

    /**
     * @param ContainerInterface $modelLocator      locator for models
     * @param ContainerInterface $repositoryLocator locator for repositories
     */
    public function __construct(
        private readonly ContainerInterface $modelLocator,
        private readonly ContainerInterface $repositoryLocator
    ) {
    }

    /**
     * get a certain document model
     *
     * @param string $className class name
     * @return DocumentModel|null null or the model
     */
    public function getDocumentModel(string $className) : ?DocumentModel
    {
        if ($this->modelLocator->has($className)) {
            return $this->modelLocator->get($className);
        }
        return null;
    }

    /**
     * get a certain document repository
     *
     * @param string $className class name
     * @return DocumentRepository|null null or the repo
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDocumentRepository(string $className) : ?DocumentRepository
    {
        if ($this->repositoryLocator->has($className)) {
            return $this->repositoryLocator->get($className);
        }
        return null;
    }
}
