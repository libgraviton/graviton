"""Composer Extension

Downloads and runs composer
"""
import os
import os.path
import logging
from build_pack_utils import utils


_log = logging.getLogger('composer')


DEFAULTS = utils.FormattedDict({
    'COMPOSER_URL': 'https://getcomposer.org/installer',
})


# Extension Methods
def preprocess_commands(ctx):
    return ()


def service_commands(ctx):
    return {}


def service_environment(ctx):
    return {}


def compile(install):
    print 'Installing composer'
    os.system('curl -sS %s | /tmp/staged/app/php/bin/php -d "extension=openssl.so" > /dev/null' % DEFAULTS['COMPOSER_URL'])
    print "Running composer install"
    os.system('export SYMFONY__VCAP__SERVICES=$VCAP_SERVICES && cd /tmp/staged/app/htdocs/ && /tmp/staged/app/php/bin/php -d "extension=openssl.so" -d "extension=mongo.so" -d "extension=curl.so" /tmp/staged/composer.phar install --no-interaction --quiet --dev')
    print "Set up MongoDB fixtures"
    os.system('export SYMFONY__VCAP__SERVICES=$VCAP_SERVICES && cd /tmp/staged/app/htdocs/ && /tmp/staged/app/php/bin/php -d "extension=openssl.so" -d "extension=mongo.so" -d "extension=curl.so" app/console doctrine:mongodb:fixtures:load 2> /dev/null')
    return 0
