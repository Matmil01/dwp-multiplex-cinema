-- Triggers are automatic database rules that run when rows are changed.
-- These prevent overlapping showings on the same screen, based on movie runtime.
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

    -- two time ranges overlap if one starts before the other ends and ends after the other starts
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