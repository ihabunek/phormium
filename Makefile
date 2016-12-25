sqlite :
	phpunit -c tests/travis/sqlite/phpunit.xml

mysql :
	phpunit -c tests/travis/mysql/phpunit.xml

postgres :
	phpunit -c tests/travis/postgres/phpunit.xml

all : sqlite mysql postgres

coverage :
	phpunit -c tests/travis/sqlite/phpunit.xml --coverage-html target/coverage --whitelist src

unit-coverage :
	phpunit -c tests/travis/sqlite/phpunit.xml --coverage-html target/coverage --whitelist src --testsuite unit

documentation :
	cd docs && make clean && make html

clean :
	rm -rf target/coverage

default: sqlite
