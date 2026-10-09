-- A view is a saved SELECT that can be queried like a table.
-- Public pages use this view so future-dated news stays hidden until its publication date.
CREATE VIEW visible_news AS
SELECT * FROM news WHERE published_at <= CURDATE() ORDER BY published_at DESC;