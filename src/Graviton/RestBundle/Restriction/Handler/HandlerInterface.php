<?php
/**
 * restriction handler interface
 */
namespace Graviton\RestBundle\Restriction\Handler;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RqlParser\Node\AbstractQueryNode;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
interface HandlerInterface
{

    /**
     * returns the name of this handler, string based id. this is referenced in the service definition.
     *
     * @return string handler name
     */
    public function getHandlerName();

    /**
     * gets the actual value or an AbstractQueryNode that is used to filter the data.
     *
     * @param DocumentRepository $repository the repository
     * @param string             $fieldPath  field path
     *
     * @return string|AbstractQueryNode string for eq() filtering or a AbstractQueryNode instance
     */
    public function getValue(DocumentRepository $repository, $fieldPath);
}
