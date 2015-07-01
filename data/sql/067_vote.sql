CREATE TABLE cake_vote_events(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mark VARCHAR (100) UNIQUE,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  place VARCHAR (300),
  published tinyint(4) NOT NULL DEFAULT '0',
	deleted tinyint(4) NOT NULL DEFAULT '0'
);

CREATE TABLE cake_candidates (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	tag INT (11),
	mobile_num VARCHAR (20),
	user_id INT NOT NULL,
	title VARCHAR(50),
	description TEXT DEFAULT NULL,
	images VARCHAR (300),
	published tinyint(4) NOT NULL DEFAULT '0',
	deleted tinyint(4) NOT NULL DEFAULT '0',
	created DATETIME DEFAULT NULL
);

CREATE TABLE cake_votes (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	candidate_id INT UNSIGNED,
	user_id INT UNSIGNED,
	event_id INT,
	created DATETIME DEFAULT NULL
);

CREATE TABLE cake_candidate_events (
  id INT NOT NULL AUTO_INCREMENT,
  event_id INT NOT NULL,
  candidate_id INT NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX `un-index` (event_id ASC, candidate_id ASC));
