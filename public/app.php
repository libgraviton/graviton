<?php

namespace Graviton;

require __DIR__.'/../app/autoload_runtime.php';

return function (array $context) {
    $env = 'prod';
    if (!empty($context['APP_ENV'])) {
        $env = $context['APP_ENV'];
    }
    if (!empty($context['SYMFONY_ENV'])) {
        $env = $context['SYMFONY_ENV'];
    }

    $debug = false;
    if (!empty($context['APP_DEBUG'])) {
        $debug = (bool) $context['APP_DEBUG'];
    }

    return new AppKernel($env, $debug);
};
