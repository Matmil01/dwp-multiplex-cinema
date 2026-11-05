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

-- No users are seeded, so no password hash lives in the repo. To make the first admin, register on the
-- site, then run: UPDATE users SET role = 'admin' WHERE email = 'the-email-you-registered';

-- Single demo film. The poster is a TMDB URL, same as films added through the admin.
INSERT INTO genres (name) VALUES
    ('Action'),
    ('Sci-Fi');

INSERT INTO movies (title, release_year, description, duration_minutes, poster_url) VALUES
    ('The Matrix', 1999, 'A computer programmer learns that his world is a simulation and joins a rebellion against the machines that run it.', 136, 'https://image.tmdb.org/t/p/w200/dXNAPwY7VrqMAo51EKhhCJfaGb5.jpg');

-- The ids used below (movie 1, genres 1-2, people 1-4) are the AUTO_INCREMENT numbers these
-- inserts get on an empty database, which is why this file must run on a fresh install only.
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

-- Times are relative to the build date: every screen shows the movie at 19:30 on each of the next
-- 7 days (today + 0..6). AutoSchedule repeats the latest week, so a full week here means every
-- day keeps a screening. TIMESTAMP(date, time) glues a date and a time together; CURDATE() is today.
-- These inserts go through the overlap trigger too — one showing per screen per day, so none collide.
INSERT INTO showings (movie_id, screen_id, show_datetime)
SELECT 1, s.id, TIMESTAMP(CURDATE() + INTERVAL d.n DAY, '19:30:00')
FROM screens s
CROSS JOIN (
    SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3
    UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) d;

INSERT INTO news (title, body, image_path, published_at) VALUES
    ('The Matrix on eternal repeat', 'The Matrix plays on all available screens today and tomorrow and probably forever.', NULL, CURDATE());