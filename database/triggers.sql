DELIMITER $$

-- Runs when a showing is added. Steps: find the new movie's runtime, work out when the new
-- showing would end, count other showings on the same screen whose time range overlaps,
-- and refuse if there are any.
CREATE TRIGGER trg_showings_no_overlap_insert
BEFORE INSERT ON showings
FOR EACH ROW
BEGIN
    DECLARE new_duration INT;
    DECLARE new_end DATETIME;
    DECLARE conflict_count INT;

    SELECT duration_minutes INTO new_duration FROM movies WHERE id = NEW.movie_id;
    SET new_end = DATE_ADD(NEW.show_datetime, INTERVAL COALESCE(new_duration, 0) MINUTE);

    -- two time ranges overlap if one starts before the other ends and ends after the other starts
    -- (e.g. new 19:00-21:00 vs existing 20:30-22:30: 19:00 < 22:30 and 21:00 > 20:30 -> overlap)
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

-- Same check when a showing is changed (moved, or its movie swapped). The only difference is
-- `s.id != NEW.id`: the showing must not be compared with its own old position.
CREATE TRIGGER trg_showings_no_overlap_update
BEFORE UPDATE ON showings
FOR EACH ROW
BEGIN
    DECLARE new_duration INT;
    DECLARE new_end DATETIME;
    DECLARE conflict_count INT;

    SELECT duration_minutes INTO new_duration FROM movies WHERE id = NEW.movie_id;
    SET new_end = DATE_ADD(NEW.show_datetime, INTERVAL COALESCE(new_duration, 0) MINUTE);

    -- two time ranges overlap if one starts before the other ends and ends after the other starts
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