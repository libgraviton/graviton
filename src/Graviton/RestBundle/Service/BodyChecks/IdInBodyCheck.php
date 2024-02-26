<?php
/**
 * IdInBodyCheck
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ServerRequestInterface;
use Rs\Json\Patch;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class IdInBodyCheck extends BodyCheckerAbstract
{

    public function check(BodyCheckData $data): void
    {
        if (empty($data->existingId)) {
            return;
        }

        $idToSet = $data->existingId;

        $setId = function(ServerRequestInterface $request) use ($idToSet) {
            $input = (string) $request->getBody();
            try {
                $patchDocument = [
                    [
                        'op' => 'add',
                        'path' => '/id',
                        'value' => $idToSet
                    ]
                ];

                $patch = new Patch($input, \json_encode($patchDocument));
                $input = $patch->apply();
            } catch (\Throwable $t) {
            }

            return $request->withBody(Utils::streamFor($input));
        };

        $data->addPayloadModifier($setId);
    }
}
