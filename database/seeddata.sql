-- Baseline data needed for a usable cinema setup.
INSERT INTO screens (type) VALUES
    ('16mm'),
    ('35mm'),
    ('70mm'),
    ('imax');

INSERT INTO company_info (id, description, opening_time, closing_time, address, phone) VALUES
    (1, 'Don''t come. We hate people.', '12:00:00', '23:59:00', '308 Negra Arroyo Lane, Albuquerque, New Mexico 87104', '(505) 503-4455');

-- Single demo film. Its poster URL is left empty so the placeholder image shows.
INSERT INTO genres (name) VALUES
    ('Action'),
    ('Sci-Fi');

INSERT INTO movies (title, release_year, description, duration_minutes) VALUES
    ('The Matrix', 1999, 'A computer programmer learns that his world is a simulation and joins a rebellion against the machines that run it.', 136);

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
    ('The Matrix Is Showing Now', 'The Matrix plays on all four screens today and tomorrow. Check the Movies page for showing times.', NULL, CURDATE());