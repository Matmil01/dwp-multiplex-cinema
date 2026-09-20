CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tmdb_id INT UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration_minutes INT,
    poster_url VARCHAR(500),
    release_year SMALLINT
);

CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE movie_genres (
    movie_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

CREATE TABLE people (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tmdb_person_id INT UNIQUE,
    name VARCHAR(255) NOT NULL
);

-- role distinguishes director/writer/cast; billing_order ranks cast (1-4) and is unused for director/writer
CREATE TABLE movie_credits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    person_id INT NOT NULL,
    role ENUM('director', 'writer', 'cast') NOT NULL,
    billing_order INT,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
);

-- one row per physical screen; type is what actually drives format (a screen only ever runs one format)
CREATE TABLE screens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL
);

CREATE TABLE showings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    screen_id INT NOT NULL,
    show_datetime DATETIME NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
);

CREATE INDEX idx_showings_show_datetime ON showings (show_datetime);
CREATE INDEX idx_movies_title ON movies (title);

-- Blocks two showings from overlapping on the same screen (based on movie runtime); this trigger is
-- the actual guardrail, the admin form just displays whatever error it raises.
DELIMITER $$

CREATE TRIGGER trg_showings_no_overlap_insert
BEFORE INSERT ON showings
FOR EACH ROW
BEGIN
    DECLARE new_duration INT;
    DECLARE new_end DATETIME;
    DECLARE conflict_count INT;

    SELECT duration_minutes INTO new_duration FROM movies WHERE id = NEW.movie_id;
    SET new_end = DATE_ADD(NEW.show_datetime, INTERVAL COALESCE(new_duration, 0) MINUTE);

    SELECT COUNT(*) INTO conflict_count
    FROM showings s
    JOIN movies m ON m.id = s.movie_id
    WHERE s.screen_id = NEW.screen_id
      AND NEW.show_datetime < DATE_ADD(s.show_datetime, INTERVAL COALESCE(m.duration_minutes, 0) MINUTE)
      AND new_end > s.show_datetime;

    IF conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'This screen is already booked for an overlapping time.';
    END IF;
END$$

CREATE TRIGGER trg_showings_no_overlap_update
BEFORE UPDATE ON showings
FOR EACH ROW
BEGIN
    DECLARE new_duration INT;
    DECLARE new_end DATETIME;
    DECLARE conflict_count INT;

    SELECT duration_minutes INTO new_duration FROM movies WHERE id = NEW.movie_id;
    SET new_end = DATE_ADD(NEW.show_datetime, INTERVAL COALESCE(new_duration, 0) MINUTE);

    SELECT COUNT(*) INTO conflict_count
    FROM showings s
    JOIN movies m ON m.id = s.movie_id
    WHERE s.screen_id = NEW.screen_id
      AND s.id != NEW.id
      AND NEW.show_datetime < DATE_ADD(s.show_datetime, INTERVAL COALESCE(m.duration_minutes, 0) MINUTE)
      AND new_end > s.show_datetime;

    IF conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'This screen is already booked for an overlapping time.';
    END IF;
END$$

DELIMITER ;

INSERT INTO screens (name, type) VALUES
    ('35mm Screen', '35mm'),
    ('70mm Screen', '70mm'),
    ('Standard Digital', 'digital'),
    ('4K Screen', '4k'),
    ('IMAX Screen', 'imax'),
    ('Drive-In Screen', 'drive-in');
