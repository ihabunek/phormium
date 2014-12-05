DROP TABLE IF EXISTS person;
CREATE TABLE person(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100),
    birthday DATE,
    salary DECIMAL(20,2)
);

DROP TABLE IF EXISTS contact;
CREATE TABLE contact(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    person_id INTEGER,
    value VARCHAR(255),
    FOREIGN KEY (person_id) REFERENCES person(id)
);

DROP TABLE IF EXISTS post;
CREATE TABLE post(
	date DATE,
	no INTEGER,
	title VARCHAR(255),
	contents VARCHAR(1024),
	PRIMARY KEY (date, no)
);

DROP TABLE IF EXISTS tag;
CREATE TABLE tag(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_date DATE,
    post_no INTEGER,
    value VARCHAR(1024),
    FOREIGN KEY (post_date, post_no) REFERENCES post(date, no)
);

