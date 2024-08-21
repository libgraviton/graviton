<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    // load optional merge config
    $mergeConfig = getenv('GRAVITON_MERGE_CONFIG');
    if (is_string($mergeConfig) && is_dir($mergeConfig)) {
        if (!str_ends_with($mergeConfig, '/')) {
            $mergeConfig .= '/';
        }
        $container->import($mergeConfig.'*.yaml');
    }
};
