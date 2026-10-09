-- Baseline data needed for a usable cinema setup.
INSERT INTO screens (type) VALUES
    ('16mm'),
    ('35mm'),
    ('70mm'),
    ('imax');

INSERT INTO company_info (id, description, opening_time, closing_time, address, phone) VALUES
    (1, 'Don''t come. We hate people.', '12:00:00', '23:59:00', '308 Negra Arroyo Lane, Albuquerque, New Mexico 87104', '(505) 503-4455');