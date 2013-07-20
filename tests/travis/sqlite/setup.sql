DROP TABLE IF EXISTS person;
CREATE TABLE person (
  id INTEGER PRIMARY KEY,
  name 	VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  birthday DATE,
  created DATETIME,
  income DECIMAL(10,2)
);

DROP TABLE IF EXISTS trade;
CREATE TABLE trade(
    tradedate DATE NOT NULL,
    tradeno INTEGER NOT NULL,
    price DECIMAL(10,2),
    quantity INTEGER,
    PRIMARY KEY(tradedate, tradeno)
);

DROP TABLE IF EXISTS pkless;
CREATE TABLE pkless (
    foo VARCHAR(20),
    bar VARCHAR(20),
    baz VARCHAR(20)
);
