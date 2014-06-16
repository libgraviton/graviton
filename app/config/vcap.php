<?php
$services = getenv('SYMFONY__VCAP__SERVICES');
if ($services) {
    $container->setParameter('vcap.services', $services);
}
