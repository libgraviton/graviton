<?php
/**
 * BodyCheckData
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use Graviton\RestBundle\Model\DocumentModel;
use Psr\Http\Message\ServerRequestInterface;
use Rs\Json\Pointer;
use Swaggest\JsonDiff\JsonDiff;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class BodyCheckData
{

    /**
     * constructor.
     *
     * @param ServerRequestInterface $request             request
     * @param DocumentModel          $model               model
     * @param string|null            $existingId          existing id
     * @param string|null            $existingSerialized  existing record as serialized
     * @param Pointer|null           $jsonExisting        json pointer for existing
     * @param JsonDiff|null          $jsonDiff            diff from old to new
     * @param \SplStack              $userPayloadModifier payload modified
     * @param \SplStack              $responseModifier    response modified
     */
    public function __construct(
        public ServerRequestInterface $request,
        public DocumentModel $model,
        public ?string $existingId,
        public ?string $existingSerialized,
        public ?Pointer $jsonExisting,
        public ?JsonDiff $jsonDiff,
        public \SplStack $userPayloadModifier = new \SplStack(),
        public \SplStack $responseModifier = new \SplStack()
    ) {
    }

    /**
     * add a payload modified
     *
     * @param callable $modifier modifier
     * @return void
     */
    public function addPayloadModifier(callable $modifier)
    {
        $this->userPayloadModifier->push($modifier);
    }

    /**
     * add response modifier
     *
     * @param callable $modifier modifier
     * @return void
     */
    public function addResponseModifier(callable $modifier)
    {
        $this->responseModifier->push($modifier);
    }

    /**
     * returns all modified field paths
     *
     * @return string[] modified paths
     */
    public function getAllModifiedFields() : array
    {
        if (is_null($this->jsonDiff)) {
            return [];
        }

        return array_unique(
            $this->jsonDiff->getAddedPaths() +
            $this->jsonDiff->getModifiedPaths() +
            $this->jsonDiff->getRemovedPaths()
        );
    }

    /**
     * converts a list with dot notation paths to jsonpatch format.
     * for array fields, this is not really possible as we cannot access all indexes.
     * so the list produced helps more with matching that is modified.
     *
     * @param array $pathList path list
     * @return array
     */
    public function pathListToPatchFormat(array $pathList) : array
    {
        return array_map(
            function ($item) {
                return '/'.str_replace('.', '/', $item);
            },
            $pathList
        );
    }

    /**
     * tells whether one list of jsonPatch fields is included in another list
     *
     * @param array $list    list
     * @param array $subList sublist
     * @return bool true if yes, false otherwise
     */
    public function isListIncludedInSublist(array $list, array $subList) : bool
    {
        $normalizeField = function ($item) {
            // ends in number
            preg_match('/^(.*)\/([0-9]+)$/', $item, $matches);
            if (!empty($matches[0])) {
                $item = str_replace($matches[2], '0', $item);
            }

            // number somewhere inside
            preg_match_all('/\/([0-9]+)\//', $item, $matches);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $match) {
                    $item = str_replace($match, '/0/', $item);
                }
            }

            return $item;
        };

        $getFieldAliases = function ($field) {
            $allFieldNames = [$field];
            if (str_starts_with($field, '/')) {
                $field = substr($field, 1);
            }
            $parts = explode('/', $field);
            $loopStr = "/";
            foreach ($parts as $part) {
                $loopStr .= $part . '/';
                $allFieldNames[] = $loopStr.'*';
            }
            return $allFieldNames;
        };

        $list = array_unique(array_map($normalizeField, $list));
        $subList = array_unique(array_map($normalizeField, $subList));

        foreach ($subList as $key => $item) {
            $allFieldNames = $getFieldAliases($item);
            if (!empty(array_intersect($list, $allFieldNames))) {
                unset($subList[$key]);
            }
        }

        return empty($subList);
    }
}
