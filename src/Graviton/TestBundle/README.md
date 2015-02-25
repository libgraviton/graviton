# Testing

## Quality Control

Graviton uses [travis-ci](http://travis-ci.org) and [scrutinizer-ci](http://scrutinizer-ci.com).

You will need to log in to scrutinizer once using you github account so you may be added to the project as admin or moderator as needed.

## Profiling graviton

If you expect performance issues you may try to find them by profiling the code.

````bash
php -d xdebug.profiler_enable=1 -d xdebug.profiler_output_dir=./ vendor/bin/phpunit -c app/
````

This generates a files called ``cachegrind.out.<PPID>`` that you may inspect using kcachegrind or a similar tool.
