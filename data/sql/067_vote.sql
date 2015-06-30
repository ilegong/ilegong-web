CREATE TABLE cake_vote_events(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mark VARCHAR (100) UNIQUE,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  place VARCHAR (300),
  published tinyint(4) NOT NULL DEFAULT '0',
	deleted tinyint(4) NOT NULL DEFAULT '0',
);

CREATE TABLE cake_candidates (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	tag INT (11),
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
	created DATETIME DEFAULT NULL
);