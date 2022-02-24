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


/*

$kernel = new AppKernel($env, $activateDebug);

if (!$activateDebug) {
    $kernel = new AppCache($kernel);
}

Request::setTrustedProxies(
    ['0.0.0.0/0'],
    Request::HEADER_FORWARDED &&
    Request::HEADER_X_FORWARDED_FOR &&
    Request::HEADER_X_FORWARDED_HOST &&
    Request::HEADER_X_FORWARDED_PROTO &&
    Request::HEADER_X_FORWARDED_PORT
);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
$*/
