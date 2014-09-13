#!/bin/bash

# Current foder
SCRIPT_DIR=$(cd "$(dirname "$0")"; pwd)

composer install

mkdir tmp

if [ $DB = "mysql" ]
then
    mysql < $SCRIPT_DIR/mysql/setup.sql
fi

if [ $DB = "sqlite" ]
then
    sqlite3 tmp/test.db < $SCRIPT_DIR/sqlite/setup.sql
fi

if [ $DB = "postgres" ]
then
    dropdb -U postgres --if-exists phormium_tests
    createdb -U postgres phormium_tests
    psql -U postgres -d phormium_tests -f $SCRIPT_DIR/postgres/setup.sql
fi
