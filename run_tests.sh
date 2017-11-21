rm src/Graviton/I18nBundle/Resources/translations/*.odm
touch src/Graviton/I18nBundle/Resources/translations/i18n.de.odm
touch src/Graviton/I18nBundle/Resources/translations/i18n.es.odm

./vendor/bin/phpunit
