# ENTB is not checked by travis so why should we? - and it's incompatible to some of the changes we made globally (@lapistrano) - uncomment to see..
#./vendor/bin/phpcs --standard=ENTB  --ignore='src/GravitonDyn/*' --ignore='src/*/Tests/*' --ignore='app/cache/*' --ignore='app/check.php' --ignore='app/SymfonyRequirements.php' --ignore='web/check.php' --ignore='web/config.php' --ignore='app/AppCache.php' --ignore='*.css' --ignore='*.js' src/ app/ web/

./vendor/bin/phpcs --standard=PSR1 --ignore='src/GravitonDyn/*' --ignore='src/*/Tests/*' --ignore='app/cache/*' --ignore='app/check.php' --ignore='app/SymfonyRequirements.php' --ignore='web/check.php' --ignore='web/config.php' --ignore='app/AppCache.php' --ignore='*.css' --ignore='*.js' src/ app/ web/
./vendor/bin/phpcs --standard=PSR2 --ignore='src/GravitonDyn/*' --ignore='src/*/Tests/*' --ignore='app/cache/*' --ignore='app/check.php' --ignore='app/SymfonyRequirements.php' --ignore='web/check.php' --ignore='web/config.php' --ignore='app/AppCache.php' --ignore='*.css' --ignore='*.js' src/ app/ web/

