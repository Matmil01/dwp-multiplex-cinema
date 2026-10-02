CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tmdb_id INT UNIQUE,
    title VARCHAR(100) NOT NULL,
    release_year SMALLINT NOT NULL,
    description TEXT,
    duration_minutes INT,
    poster_url VARCHAR(150)
) ENGINE = InnoDB;

CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE movie_genres (
    movie_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE people (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tmdb_person_id INT UNIQUE,
    name VARCHAR(100) NOT NULL
) ENGINE = InnoDB;

-- role distinguishes director/writer/cast; billing_order ranks cast (1-4) and is unused for director/writer
CREATE TABLE movie_credits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    person_id INT NOT NULL,
    role ENUM('director', 'writer', 'cast') NOT NULL,
    billing_order INT,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- one row per physical screen, identified only by its format (no separate display name column)
CREATE TABLE screens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(10) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE showings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    screen_id INT NOT NULL,
    show_datetime DATETIME NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- TO DO: NEWS. Standalone, no relation to the movies/screens/showings tables above
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    image_path VARCHAR(150),
    published_at DATETIME NOT NULL
) ENGINE = InnoDB;

-- single-row table holding admin-editable company info
CREATE TABLE company_info (
    id INT PRIMARY KEY DEFAULT 1,
    description TEXT,
    opening_time TIME NOT NULL,
    closing_time TIME NOT NULL,
    address VARCHAR(150),
    phone VARCHAR(30),
    CHECK (opening_time < closing_time)
) ENGINE = InnoDB;

CREATE INDEX idx_showings_show_datetime ON showings (show_datetime);
CREATE INDEX idx_movies_title ON movies (title);

-- Public pages read from this instead of news directly, so future-dated posts stay hidden until then.
CREATE VIEW visible_news AS
SELECT * FROM news WHERE published_at <= NOW() ORDER BY published_at DESC;

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

INSERT INTO screens (type) VALUES
    ('16mm'),
    ('35mm'),
    ('70mm'),
    ('imax');

INSERT INTO company_info (id, description, opening_time, closing_time, address, phone) VALUES
    (1, 'Don''t come. We hate people.', '12:00:00', '23:59:00', '308 Negra Arroyo Lane, Albuquerque, New Mexico 87104', '(505) 503-4455');
