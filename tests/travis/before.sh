#!/bin/sh

composer install

mkdir tmp

if [ '$DB' = 'mysql' ]; then
    mysql < tests/travis/mysql/setup.sql
fi

if [ '$DB' = 'sqlite' ]; then
    sqlite3 tmp/test.db < tests/travis/sqlite/setup.sql
fi

if [ '$DB' = 'postgres' ]; then
    psql -c 'drop database if exists phormium_tests;' -U postgres
    psql -c 'create database phormium_tests;' -U postgres
    psql -f tests/travis/postgres/setup.sql -d phormium_tests -U postgres
fi
