<?php
/**
 * BodyChecker
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use Graviton\RestBundle\Model\DocumentModel;
use Swaggest\JsonDiff\JsonDiff;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
interface BodyCheckerInterface
{

    public function check(Request $request, DocumentModel $model, ?string $existingId, ?string $existingSerialized, ?JsonDiff $jsonDiff);

}
