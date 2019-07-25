test: 
	vendor/bin/phpunit tests

test7.1:
	/usr/bin/php7.1 vendor/bin/phpunit tests

coverage:
	vendor/bin/phpunit --configuration tests/php-unit.xml --coverage-html tests/_output tests