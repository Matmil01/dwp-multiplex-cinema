CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tmdb_id INT UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration_minutes INT,
    poster_url VARCHAR(500),
    release_date DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
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
    type VARCHAR(50) NOT NULL,
    capacity INT
);

CREATE TABLE showings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    screen_id INT NOT NULL,
    show_datetime DATETIME NOT NULL,
    price DECIMAL(6, 2),
    is_daily_premiere BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
);

CREATE INDEX idx_showings_show_datetime ON showings (show_datetime);
CREATE INDEX idx_movies_title ON movies (title);

INSERT INTO screens (name, type, capacity) VALUES
    ('Standard Digital', 'digital', 120),
    ('35mm Screen', '35mm', 80),
    ('16mm Screen', '16mm', 40),
    ('70mm Screen', '70mm', 100),
    ('IMAX Screen', 'imax', 150),
    ('Drive-In Screen', 'drive-in', 60);
