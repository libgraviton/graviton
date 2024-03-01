<?php
/**
 * ReadOnlyFieldsBodyCheck
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ServerRequestInterface;
use Rs\Json\Patch;
use Rs\Json\Pointer;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class VersionedServiceBodyCheck extends BodyCheckerAbstract
{

    /** DB Field name used for validation and incremental */
    const string FIELD_NAME = 'version';

    public function check(BodyCheckData $data): void
    {
        if (!$data->model->getRuntimeDefinition()->isVersioned()) {
            return;
        }

        $currentVersion = null;
        if (!empty($data->jsonExisting)) {
            try {
                $currentVersion = $data->jsonExisting->get('/'.self::FIELD_NAME);
            } catch (\Throwable $t) {
            }
        }

        $userVersion = null;
        $payload = new Pointer((string) $data->request->getBody());
        try {
            $userVersion = $payload->get('/'.self::FIELD_NAME);
        } catch (\Throwable $t) {
        }

        if (!empty($currentVersion) && $currentVersion != $userVersion) {
            throw new BodyCheckViolation(
                'The value you provided does not match current version of the document.',
                self::FIELD_NAME
            );
        }

        // if we are here, all is fine! increment by one!
        if ($currentVersion < 1) {
            $currentVersion = 0; // init
        }

        // add a function to increment version by 1!
        $setVersion = $currentVersion + 1;

        $incrementor = function ($version) {
            return function (ServerRequestInterface $request) use ($version) {
                $patchDocument = [
                    [
                        'op' => 'add',
                        'path' => '/'.self::FIELD_NAME,
                        'value' => 1
                    ],
                    [
                        'op' => 'replace',
                        'path' => '/'.self::FIELD_NAME,
                        'value' => $version
                    ]
                ];

                $patch = new Patch((string) $request->getBody(), \json_encode($patchDocument));
                $patchedDocument = $patch->apply();

                return $request->withBody(Utils::streamFor($patchedDocument));
            };
        };

        $data->addPayloadModifier($incrementor($setVersion));
    }
}
