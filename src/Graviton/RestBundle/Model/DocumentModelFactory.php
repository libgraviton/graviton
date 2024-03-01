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

    public function __construct(
        private QueryService $queryService,
        private EventDispatcherInterface $eventDispatcher,
        private RestUtils $restUtils,
        private SecurityUtils $securityUtils,
        private DocumentManager $documentManager
    ) {
    }

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
