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

DROP TABLE IF EXISTS contact;
CREATE TABLE contact(
    id INTEGER NOT NULL AUTO_INCREMENT,
    person_id INTEGER NOT NULL,
    value VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (person_id) REFERENCES person(id)
);

DROP TABLE IF EXISTS asset;
CREATE TABLE asset(
    id INTEGER NOT NULL AUTO_INCREMENT,
    owner_id INTEGER NOT NULL,
    value VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (owner_id) REFERENCES person(id)
);

CREATE TABLE trade(
    tradedate DATE NOT NULL,
    tradeno INTEGER NOT NULL,
    price DECIMAL(10,2),
    quantity INTEGER,
    PRIMARY KEY(tradedate, tradeno)
);

CREATE TABLE pkless (
    foo VARCHAR(20),
    bar VARCHAR(20),
    baz VARCHAR(20)
);
