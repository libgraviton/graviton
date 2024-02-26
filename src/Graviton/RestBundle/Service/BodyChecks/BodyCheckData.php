<?php
/**
 * BodyCheckData
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use Graviton\RestBundle\Model\DocumentModel;
use Rs\Json\Pointer;
use Swaggest\JsonDiff\JsonDiff;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class BodyCheckData
{

     public function __construct(
         public Request $request,
         public DocumentModel $model,
         public ?string $existingId,
         public ?string $existingSerialized,
         public ?Pointer $jsonExisting,
         public ?JsonDiff $jsonDiff
     ) {}

    /**
     * returns all modified field paths
     *
     * @return array
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
     * @param array $pathList
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
     * @param array $list
     * @param array $subList
     * @return bool
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

        $list = array_unique(array_map($normalizeField, $list));
        $subList = array_unique(array_map($normalizeField, $subList));

        foreach ($subList as $key => $item) {
            if (in_array($item, $list)) {
                unset($subList[$key]);
            }
        }

        return empty($subList);
    }

}
