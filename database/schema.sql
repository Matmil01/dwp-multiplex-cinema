SET default_storage_engine=InnoDB;

CREATE TABLE movies (
   id INT AUTO_INCREMENT PRIMARY KEY,
   tmdb_id INT UNIQUE,
   title VARCHAR(100) NOT NULL,
   release_year SMALLINT NOT NULL,
   description TEXT,
   duration_minutes INT,
   poster_url VARCHAR(150)
);

CREATE TABLE genres (
   id INT AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(20) NOT NULL UNIQUE
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
   name VARCHAR(100) NOT NULL
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

-- one row per physical screen, identified only by its format (no separate display name column)
CREATE TABLE screens (
   id INT AUTO_INCREMENT PRIMARY KEY,
   type VARCHAR(10) NOT NULL
);

CREATE TABLE showings (
   id INT AUTO_INCREMENT PRIMARY KEY,
   movie_id INT NOT NULL,
   screen_id INT NOT NULL,
   show_datetime DATETIME NOT NULL,
   FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
   FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
);

-- TO DO: NEWS. Standalone, no relation to the movies/screens/showings tables above
CREATE TABLE news (
   id INT AUTO_INCREMENT PRIMARY KEY,
   title VARCHAR(100) NOT NULL,
   body TEXT NOT NULL,
   image_path VARCHAR(150),
   published_at DATE NOT NULL
);

-- single-row table holding admin-editable company info
CREATE TABLE company_info (
   id INT PRIMARY KEY DEFAULT 1,
   description TEXT,
   opening_time TIME NOT NULL,
   closing_time TIME NOT NULL,
   address VARCHAR(150),
   phone VARCHAR(30),
   CHECK (opening_time < closing_time)
);

CREATE INDEX idx_showings_show_datetime ON showings (show_datetime);
CREATE INDEX idx_movies_title ON movies (title);
