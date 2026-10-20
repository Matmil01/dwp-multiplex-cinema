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
-- seat_rows/seats_per_row/aisle_after describe the seating grid; seats themselves are not stored per screen
CREATE TABLE screens (
   id INT AUTO_INCREMENT PRIMARY KEY,
   type VARCHAR(10) NOT NULL,
   seat_rows TINYINT NOT NULL,
   seats_per_row TINYINT NOT NULL,
   aisle_after TINYINT NOT NULL,
   ticket_price DECIMAL(6,2) NOT NULL
);

CREATE TABLE showings (
   id INT AUTO_INCREMENT PRIMARY KEY,
   movie_id INT NOT NULL,
   screen_id INT NOT NULL,
   show_datetime DATETIME NOT NULL,
   FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
   FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
);

-- news table is standalone, no relation to the movies/screens/showings tables above
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
   tagline VARCHAR(150) NOT NULL,
   description TEXT,
   opening_time TIME NOT NULL,
   closing_time TIME NOT NULL,
   address VARCHAR(150),
   phone VARCHAR(30),
   CHECK (opening_time < closing_time)
);

-- password_hash holds the output of PHP password_hash(), never the plain password
CREATE TABLE users (
   id INT AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(100) NOT NULL,
   email VARCHAR(150) NOT NULL UNIQUE,
   password_hash VARCHAR(255) NOT NULL,
   role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer'
);

-- price_per_seat is copied from the screen at booking time so old invoices don't change when prices do
CREATE TABLE bookings (
   id INT AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   showing_id INT NOT NULL,
   status ENUM('paid', 'cancelled') NOT NULL DEFAULT 'paid',
   price_per_seat DECIMAL(6,2) NOT NULL,
   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (user_id) REFERENCES users(id),
   FOREIGN KEY (showing_id) REFERENCES showings(id)
);

-- showing_id repeats what bookings already knows (a deliberate 3NF exception): the UNIQUE key below needs it
-- to stop two bookings holding the same seat. Cancelling a booking deletes its seats, which frees them.
CREATE TABLE booking_seats (
   id INT AUTO_INCREMENT PRIMARY KEY,
   booking_id INT NOT NULL,
   showing_id INT NOT NULL,
   seat_row CHAR(1) NOT NULL,
   seat_number TINYINT NOT NULL,
   UNIQUE (showing_id, seat_row, seat_number),
   FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
   FOREIGN KEY (showing_id) REFERENCES showings(id)
);

CREATE INDEX idx_showings_show_datetime ON showings (show_datetime);
CREATE INDEX idx_movies_title ON movies (title);
