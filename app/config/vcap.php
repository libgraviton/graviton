<?php
/**
 * load config from VCAP variables
 *
 * This is here because there was no easiery way to load parameters from an environment
 * variable. Feel free to replace this if you know about a better was to get some basic
 * ENV variables (as injected by bosh or similar) into the containers param stack.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
$container->setParameter('vcap.services', getenv('SYMFONY__VCAP__SERVICES'));
