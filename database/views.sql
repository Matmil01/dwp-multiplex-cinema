CREATE VIEW visible_news AS
SELECT * FROM news WHERE published_at <= CURDATE() ORDER BY published_at DESC;

CREATE VIEW today_showings AS
SELECT s.id, s.show_datetime, m.id AS movie_id, m.title, m.release_year, m.duration_minutes, m.poster_url,
	   sc.type AS screen_type,
	   GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
FROM showings s
JOIN movies m ON m.id = s.movie_id
JOIN screens sc ON sc.id = s.screen_id
LEFT JOIN movie_genres mg ON mg.movie_id = m.id
LEFT JOIN genres g ON g.id = mg.genre_id
WHERE DATE(s.show_datetime) = CURDATE()
GROUP BY s.id, m.id, m.title, m.release_year, m.duration_minutes, m.poster_url, sc.type;