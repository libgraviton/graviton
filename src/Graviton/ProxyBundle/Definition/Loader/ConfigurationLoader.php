<?php

namespace Graviton\ProxyBundle\Definition\Loader;


use Doctrine\Common\Cache\CacheProvider;
use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class ConfigurationLoader
 *
 * @package Graviton\ProxyBundle\Definition\Loader
 */
class ConfigurationLoader implements LoaderInterface
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var  DispersalStrategyInterface */
    private $strategy;

    /** @var array  */
    private $options = [];


    /**
     * constructor
     *
     * @param ValidatorInterface $validator validator
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function setOptions($options)
    {
        if (!empty($options['prefix'])) {
            $options['storeKey'] = $options['prefix'];
            unset($options['prefix']);
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * @inheritDoc
     */
    public function setDispersalStrategy(DispersalStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @inheritDoc
     */
    public function setCache(CacheProvider $cache, $cacheNamespace, $cacheLifetime)
    {
    }

    /**
     * Determines, if the current loader is capable of handling the request.
     *
     * @param string $url
     *
     * @return bool
     */
    public function supports($url)
    {
        $error = $this->validator->validate($url, [new Url()]);

        return 0 === count($error);
    }

    /**
     * @inheritDoc
     */
    public function load($url)
    {
        $apiDef =  new ApiDefinition();
        $apiDef->setHost(parse_url($url, PHP_URL_HOST));
        $apiDef->setBasePath(parse_url($url, PHP_URL_PATH));

        $this->defineSchema($apiDef);
        $apiDef->addEndpoint($this->options['endpoint'] . '/');

        return $apiDef;
    }

    private function defineSchema(ApiDefinition $apiDef)
    {
        if (array_key_exists('endpoint', $this->options)) {

            $finder = new Finder();
            $finder->files()->in(__DIR__ .'/../../Resources/schema/'. $this->options['storeKey']);

            foreach ($finder as $file) {
                $endpoint = $this->options['endpoint'];

                // MAGIC happens here:
                // need to streamline endpoint and filename to be able to find the endpoint.
                $cmp = str_replace('/', '', $endpoint);
                list($filename, ) = explode('.', $file->getFilename());

                if ($cmp == $filename) {
                    $schema = json_decode(file_get_contents($file->getRealPath()));
                    $apiDef->addSchema($endpoint, $schema);
                }
            }
        }
    }
}
