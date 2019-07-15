test: 
	vendor/bin/phpunit tests

coverage:
	vendor/bin/phpunit --configuration tests/php-unit.xml --coverage-html tests/_output tests