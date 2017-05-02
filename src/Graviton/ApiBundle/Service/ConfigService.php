<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 04.04.17
 * Time: 09:50
 */
namespace Graviton\ApiBundle\Service;

use Graviton\JsonSchemaBundle\Validator\InvalidJsonException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ConfigService
 * To provide a unique entry for Configuration data and caching
 *
 * @package Graviton\ApiBundle\Service
 */
class ConfigService
{
    /** @var string Where services are located */
    protected $dirService;

    /**
     * TODO Build save to cache for schema trees and so.
     * @var string Where we will cache definitions trees */
    protected $dirCache;

    public function __construct(
        $serviceDir,
        $cacheDir
    ) {
        $this->dirService = $serviceDir;
        if (strpos($this->dirService, 'vendor/graviton/graviton') ) {
            $this->dirService = str_replace('vendor/graviton/graviton/', '', $this->dirService);
        }
        $this->dirCache = $cacheDir;
    }

    public function getJsonFromFile($fileName)
    {
        $string = file_get_contents($this->dirService . DIRECTORY_SEPARATOR . $fileName);
        if (!$string) {
            throw new ServiceNotFoundException('Service not found');
        }
        $json = json_decode($string);
        if (json_last_error()) {
            throw new InvalidJsonException('Service error, '.json_last_error_msg());
        }
        return $json;
    }

}