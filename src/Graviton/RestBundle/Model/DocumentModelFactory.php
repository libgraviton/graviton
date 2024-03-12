<?php
/**
 * factory for document models
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\RestBundle\Service\QueryService;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class DocumentModelFactory
{

    /**
     * constructor.
     *
     * @param QueryService             $queryService    query service
     * @param EventDispatcherInterface $eventDispatcher dispatcher
     * @param RestUtils                $restUtils       rest utils
     * @param SecurityUtils            $securityUtils   security utils
     * @param DocumentManager          $documentManager doc manager
     */
    public function __construct(
        private QueryService $queryService,
        private EventDispatcherInterface $eventDispatcher,
        private RestUtils $restUtils,
        private SecurityUtils $securityUtils,
        private DocumentManager $documentManager
    ) {
    }

    /**
     * create a DocumentModel
     *
     * @param string $schemaPath        path to schema file
     * @param string $runtimeDefFile    path to rd file
     * @param string $documentClassName class name
     * @return DocumentModel model
     */
    public function createInstance(
        string $schemaPath,
        string $runtimeDefFile,
        string $documentClassName
    ) : DocumentModel {
        return new DocumentModel(
            $this->queryService,
            $this->eventDispatcher,
            $this->restUtils,
            $this->securityUtils,
            $this->documentManager,
            $schemaPath,
            $runtimeDefFile,
            $documentClassName
        );
    }
}
