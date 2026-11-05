SET default_storage_engine=InnoDB;

-- tmdb_id is the movie's id at TMDB, kept so an import can't create the same movie twice.
CREATE TABLE movies (
   id INT AUTO_INCREMENT PRIMARY KEY,
   tmdb_id INT UNIQUE,
   title VARCHAR(100) NOT NULL,
   release_year SMALLINT NOT NULL,
   description TEXT,
   duration_minutes INT,
   poster_url VARCHAR(150)
);

-- Lookup table: each genre name exists once, no matter how many movies use it.
CREATE TABLE genres (
   id INT AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(20) NOT NULL UNIQUE
);

-- Link table for the many-to-many between movies and genres: each row just says
-- "movie X has genre Y". The PRIMARY KEY on both columns together means the same pair
-- can't be inserted twice.
CREATE TABLE movie_genres (
   movie_id INT NOT NULL,
   genre_id INT NOT NULL,
   PRIMARY KEY (movie_id, genre_id),
   FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
   FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

-- Lookup table for directors, writers and actors. One row per person, shared across movies.
CREATE TABLE people (
   id INT AUTO_INCREMENT PRIMARY KEY,
   tmdb_person_id INT UNIQUE,
   name VARCHAR(100) NOT NULL
);

-- Link table between movies and people, like movie_genres but with extra information per link:
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
-- (the seat picker draws the grid from these three numbers). ticket_price: one price per screen.
CREATE TABLE screens (
   id INT AUTO_INCREMENT PRIMARY KEY,
   type VARCHAR(10) NOT NULL,
   seat_rows TINYINT NOT NULL,
   seats_per_row TINYINT NOT NULL,
   aisle_after TINYINT NOT NULL,
   ticket_price DECIMAL(6,2) NOT NULL
);

-- A showing = one movie, on one screen, starting at one date+time. The end time is not
-- stored; it is worked out from the movie's duration_minutes when needed (see triggers.sql).
-- Deleting a movie or screen removes its showings too (CASCADE).
CREATE TABLE showings (
   id INT AUTO_INCREMENT PRIMARY KEY,
   movie_id INT NOT NULL,
   screen_id INT NOT NULL,
   show_datetime DATETIME NOT NULL,
   FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
   FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
);

-- news table is standalone, no relation to the movies/screens/showings tables above
-- published_at gates public visibility: future dates stay hidden (see visible_news in views.sql).
-- image_path is the uploaded image's path inside public/, not the image itself.
CREATE TABLE news (
   id INT AUTO_INCREMENT PRIMARY KEY,
   title VARCHAR(100) NOT NULL,
   body TEXT NOT NULL,
   image_path VARCHAR(150),
   published_at DATE NOT NULL
);

-- single-row table holding admin-editable company info
-- There is one cinema, so there is one row, always id = 1 (no AUTO_INCREMENT). The CHECK
-- rule makes the database itself refuse a closing time earlier than the opening time.
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
-- failed_logins counts wrong passwords in a row; locked_until blocks login until that time
-- email is UNIQUE so two accounts can't share one address. role defaults to 'customer';
-- the single admin is created by seeddata.sql, never through the register form.
CREATE TABLE users (
   id INT AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(100) NOT NULL,
   email VARCHAR(150) NOT NULL UNIQUE,
   password_hash VARCHAR(255) NOT NULL,
   role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
   failed_logins TINYINT NOT NULL DEFAULT 0,
   locked_until DATETIME NULL
);

-- price_per_seat is copied from the screen at booking time so old invoices don't change when prices do
-- No ON DELETE CASCADE here on purpose: a user or showing with bookings cannot be deleted
-- (the database refuses), so paid bookings never silently disappear. status is 'paid' or
-- 'cancelled'; cancelled rows are kept for history. created_at fills itself in automatically.
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

-- One row per seat in a booking (seat_row 'B' + seat_number 4 = seat B4).
-- showing_id repeats what bookings already knows (a deliberate 3NF exception): the UNIQUE key below needs it
-- to stop two bookings holding the same seat. Cancelling a booking deletes its seats, which frees them.
-- (3NF = "third normal form", a design rule saying don't store the same fact twice. It is bent
-- here because the UNIQUE rule can only look at columns in THIS table, and it is the one thing
-- that makes double-booking a seat truly impossible, even for two requests at the same instant.)
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

-- INDEXES: an index is a sorted lookup list the database keeps for a column, like the index
-- at the back of a book. Without one, finding rows by that column means reading the whole
-- table. These two cover the most common searches: showings filtered/sorted by time (every
-- schedule query) and movies sorted/searched by title (catalog lists, the admin filter).
-- The exam brief asks for at least two indexes used by the site.
CREATE INDEX idx_showings_show_datetime ON showings (show_datetime);
CREATE INDEX idx_movies_title ON movies (title);
