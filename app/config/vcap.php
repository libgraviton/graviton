<?php
/**
 * load config from VCAP variables
 *
 * this one reads out the VCAP_SERVICES variable and sets the according
 * params on the container. this is a low-level approach that seems to work under all
 * conditions (also i.e. when a Composer\Command wants to connect)
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
$services = getenv('VCAP_SERVICES');

if (!empty($services)) {
    $services = json_decode($services, true);
    $mongo = $services['mongodb-2.2'][0]['credentials'];

    $container->setParameter('mongodb.default.server.uri', $mongo['url']);
    $container->setParameter('mongodb.default.server.db', $mongo['db']);
} else {
    $container->setParameter(
        'mongodb.default.server.uri',
        $container->getParameter('graviton.mongodb.default.server.uri')
    );
    $container->setParameter(
        'mongodb.default.server.db',
        $container->getParameter('graviton.mongodb.default.server.db')
    );
}
