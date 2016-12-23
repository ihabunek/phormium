DROP TABLE IF EXISTS person;
CREATE TABLE person (
    id SERIAL,
    name 	VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    birthday DATE,
    created TIMESTAMP,
    income DECIMAL(10,2),
    is_cool BOOLEAN,
    PRIMARY KEY (id)
);

DROP TABLE IF EXISTS contact;
CREATE TABLE contact(
    id SERIAL,
    person_id INTEGER NOT NULL,
    value VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (person_id) REFERENCES person(id)
);

DROP TABLE IF EXISTS asset;
CREATE TABLE asset(
    id SERIAL,
    owner_id INTEGER NOT NULL,
    value VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (owner_id) REFERENCES person(id)
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
