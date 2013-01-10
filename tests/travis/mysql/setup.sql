DROP DATABASE IF EXISTS phormium_tests;
CREATE DATABASE phormium_tests;

USE phormium_tests;

CREATE TABLE person (
  id INTEGER NOT NULL AUTO_INCREMENT,
  name 	VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  birthday DATE,
  created DATETIME,
  income DECIMAL(10,2),
  PRIMARY KEY (id)
);

CREATE TABLE trade(
    tradedate DATE NOT NULL,
    tradeno INTEGER NOT NULL,
    datetime DATETIME,
    price DECIMAL(10,2),
    quantity INTEGER,
    PRIMARY KEY(tradedate, tradeno)
);

CREATE TABLE pkless (
    foo VARCHAR(20),
    bar VARCHAR(20),
    baz VARCHAR(20)
);
