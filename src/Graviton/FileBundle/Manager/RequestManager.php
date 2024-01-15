<?php
/**
 * Handles REQUEST file specific actions
 */

namespace Graviton\FileBundle\Manager;

use Riverline\MultiPartParser\Converters\HttpFoundation;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RequestManager
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RequestManager constructor.
     *
     * @param RequestStack $requestStack To get the original request
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Simple RAW http request parser.
     * Ideal for PUT requests where PUT is streamed but you still need the data.
     *
     * @param Request $request Sf data request
     * @return Request
     */
    public function updateFileRequest(Request $request)
    {
        $original = $this->requestStack->getMainRequest();
        $input = $original ? $original->getContent() : false;

        if (!$input) {
            return $request;
        }

        $part = HttpFoundation::convert($request);

        if ($part->isMultiPart()) {
            // do we have metadata? for a multipart form
            $metadata = $part->getPartsByName('metadata');
            if (is_array($metadata) && !empty($metadata)) {
                $request->request->set('metadata', $metadata[0]->getBody());
            }

            // the file itself
            $upload = $part->getPartsByName('upload');
            if (is_array($upload) && !empty($upload)) {
                $uploadPart = $upload[0];

                $file = $this->extractFileFromString(
                    $uploadPart->getBody(),
                    $uploadPart->getFileName()
                );

                $request->files->add([$file]);
            }
        } elseif ((strpos($request->headers->get('Content-Type'), 'application/json') !== false)
            && $json = json_decode($part->getBody(), true)
        ) {
            // Type json and can be parsed
            $request->request->set('metadata', json_encode($json));
        } else {
            // Anything else should be saved as file
            $file = $this->extractFileFromString($part->getBody());
            if ($file) {
                $request->files->add([$file]);
            }
        }

        return $request;
    }

    /**
     * Extracts file data from request content
     *
     * @param string $fileContent      the file content
     * @param string $originalFileName an overriding original file name
     *
     * @return false|UploadedFile
     */
    private function extractFileFromString($fileContent, $originalFileName = null)
    {
        $tmpName = $fileName = uniqid(true);

        if (!is_null($originalFileName)) {
            $fileName = $originalFileName;
        }

        $dir = ini_get('upload_tmp_dir');
        $dir = (empty($dir)) ? sys_get_temp_dir() : $dir;
        $tmpFile = $dir . DIRECTORY_SEPARATOR . $tmpName;

        // create temporary file;
        (new Filesystem())->dumpFile($tmpFile, $fileContent);
        $file = new File($tmpFile);

        return new UploadedFile(
            $file->getRealPath(),
            $fileName,
            $file->getMimeType()
        );
    }
}
