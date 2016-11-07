<?php
/**
 * Handles REQUEST file specific actions
 */

namespace Graviton\FileBundle\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RequestManager
{
    /**
     * Simple RAW http request parser.
     * Ideal for PUT requests where PUT is streamed but you still need the data.
     *
     * @param Request $request Sf data request
     * @return Request
     */
    public function updateFileRequest(Request $request)
    {
        $input = $request->getContent();
        if (!$input) {
            return $request;
        }
        $server = $request->server;
        $contentType = $server->get('CONTENT_TYPE', $server->get('HTTP_CONTENT_TYPE'));
        $data = [];

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $contentType, $matches);

        // content type is probably regular form-encoded
        if (!count($matches)) {
            $json = json_decode($input, true);
            // Check if content is binary, convert to file upload
            if (!$json && $request->files->count() == 0) {
                $file = $this->extractFileFromString($input, $contentType);
                if ($file) {
                    $request->files->add([$file]);
                }
            } elseif ($json) {
                $request->request->set('metadata', json_encode($json));
            }
            return $request;
        }

        $contentBlocks = preg_split("/-+$matches[1]/", $input);

        // determine content blocks usage
        foreach ($contentBlocks as $contentBlock) {
            if (empty($contentBlock)) {
                continue;
            }
            preg_match('/name=\"(.*?)\"[^"]/i', $contentBlock, $matches);
            $name = isset($matches[1]) ? $matches[1] : '';
            if ('upload' !== $name) {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $contentBlock, $matches);
                $contentBlock = array_key_exists(2, $matches) ? $matches[2]: $contentBlock;
            }
            $data[$name] = $contentBlock;
        }

        if (array_key_exists('metadata', $data)) {
            $request->request->set('metadata', $data['metadata']);
        }
        if (array_key_exists('upload', $data)) {
            $file = $this->extractFileFromString($data['upload']);
            $request->files->add([$file]);
        }

        return $request;
    }


    /**
     * Extracts file data from request content
     *
     * @param string $fileInfoString Information about uploaded files.
     * @param string $contentType    Optional type of string
     *
     * @return false|UploadedFile
     */
    private function extractFileFromString($fileInfoString, $contentType = '')
    {
        $str = (string) $fileInfoString;
        $tmpName = $fileName = microtime() . '_' . md5($str);

        if ((preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0) || $contentType) {
            $fileContent = $str;
        } else {
            // Original Name
            preg_match('/filename=\"(.*?)\"/i', $str, $matches);
            if (array_key_exists(1, $matches)) {
                $fileName = preg_replace('/\s+/', '-', $matches[1]);
            }
            $fileInfo = explode("\r\n\r\n", ltrim($str), 2);
            if (!array_key_exists(1, $fileInfo)) {
                return false;
            }
            $fileContent = substr($fileInfo[1], 0, -2);
        }

        $dir = ini_get('upload_tmp_dir');
        $dir = (empty($dir)) ? sys_get_temp_dir() : $dir;
        $tmpFile = $dir . DIRECTORY_SEPARATOR . $tmpName;

        // create temporary file;
        $filesystem = new Filesystem();
        $filesystem->dumpFile($tmpFile, $fileContent);
        $file = new File($tmpFile);

        return new UploadedFile(
            $file->getRealPath(),
            $fileName,
            $file->getMimeType(),
            $file->getSize()
        );
    }
}
