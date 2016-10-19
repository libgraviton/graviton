<?php

namespace Graviton\ProxyBundle\Definition\Loader;


use Doctrine\Common\Cache\CacheProvider;
use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Psr\Log\LoggerInterface;
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

        return $apiDef;
    }
}
