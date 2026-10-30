-- Baseline data needed for a usable cinema setup.
INSERT INTO screens (type, seat_rows, seats_per_row, aisle_after, ticket_price) VALUES
    ('16mm', 5, 8, 4, 75.00),
    ('35mm', 8, 12, 6, 95.00),
    ('70mm', 10, 14, 7, 115.00),
    ('imax', 12, 16, 8, 145.00);

INSERT INTO company_info (id, tagline, description, opening_time, closing_time, address, phone) VALUES
    (1, 'The future sucks. Let''s go back to the 1990s.',
    'A return to the 1990s peak of the motion picture industry, Untitled Cinema is a revival house in suburban Albuquerque, New Mexico. We show only films from 1990 to 1999, each on its own projector: 16mm, 35mm, 70mm or IMAX. Whether you grew up on these films or are seeing them for the first time, come watch them the way they were meant to be seen.',
    '12:00:00', '23:59:00',
    '308 Negra Arroyo Lane, Albuquerque, New Mexico 87104', 
    '(505) 503-4455');

-- Demo admin login: admin@cinema.com / admin (password stored as a bcrypt hash from password_hash()).
INSERT INTO users (name, email, password_hash, role) VALUES
    ('Owner',
    'admin@cinema.com',
    '$2y$10$3D0JJnZWat48jF5BKfi.wuKLYRcsk036AuOmC9JAWcLzFfd3XLH5C',
    'admin');

-- Single demo film. The poster is a TMDB URL, same as films added through the admin.
INSERT INTO genres (name) VALUES
    ('Action'),
    ('Sci-Fi');

INSERT INTO movies (title, release_year, description, duration_minutes, poster_url) VALUES
    ('The Matrix', 1999, 'A computer programmer learns that his world is a simulation and joins a rebellion against the machines that run it.', 136, 'https://image.tmdb.org/t/p/w200/dXNAPwY7VrqMAo51EKhhCJfaGb5.jpg');

INSERT INTO movie_genres (movie_id, genre_id) VALUES
    (1, 1), (1, 2);

INSERT INTO people (name) VALUES
    ('Lana Wachowski'),
    ('Lilly Wachowski'),
    ('Keanu Reeves'),
    ('Laurence Fishburne');

INSERT INTO movie_credits (movie_id, person_id, role, billing_order) VALUES
    (1, 1, 'director', NULL),
    (1, 2, 'director', NULL),
    (1, 3, 'cast', 1),
    (1, 4, 'cast', 2);

-- Times are relative to the build date so the demo always has tonight and tomorrow.
INSERT INTO showings (movie_id, screen_id, show_datetime) VALUES
    (1, 1, TIMESTAMP(CURDATE(), '19:30:00')),
    (1, 2, TIMESTAMP(CURDATE(), '19:30:00')),
    (1, 3, TIMESTAMP(CURDATE(), '19:30:00')),
    (1, 4, TIMESTAMP(CURDATE(), '19:30:00')),
    (1, 1, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '19:30:00')),
    (1, 2, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '19:30:00')),
    (1, 3, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '19:30:00')),
    (1, 4, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '19:30:00'));

INSERT INTO news (title, body, image_path, published_at) VALUES
    ('The Matrix on eternal repeat', 'The Matrix plays on all available screens today and tomorrow and probably forever.', NULL, CURDATE());