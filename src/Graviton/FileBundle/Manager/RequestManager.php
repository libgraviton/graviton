<?php
/**
 * Handles REQUEST file specific actions
 */

namespace Graviton\FileBundle\Manager;

use Riverline\MultiPartParser\Part;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        $original = $this->requestStack->getMasterRequest();
        $input = $original ? $original->getContent() : false;

        if (!$input) {
            return $request;
        }

        $part = new Part((string) $request);

        if ($part->isMultiPart()) {
            // do we have metadata?
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
        } else {
            // see if body is json or binary..
            $json = json_decode($part->getBody(), true);

            // Content Type
            $contentType = $request->headers->get('Content-Type');

            // Check if content is binary, convert to file upload
            if (!$json && $request->files->count() == 0) {
                $file = $this->extractFileFromString($part->getBody());
                if ($file) {
                    $request->files->add([$file]);
                }
            } elseif ($json && $contentType != 'application/javascript') {
                $request->request->set('metadata', json_encode($json));
            } else {
                $file = $this->extractFileFromString($part->getBody());
                if ($file) {
                    $request->files->add([$file]);
                }
            }
            return $request;
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
            $file->getMimeType(),
            $file->getSize()
        );
    }
}
