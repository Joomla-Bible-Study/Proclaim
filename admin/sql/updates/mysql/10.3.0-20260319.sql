-- Landing ordering migration handled by proclaim.script.php
-- Column alterations use IF EXISTS which requires MySQL 8.0.1+
-- so they are performed in PHP instead of raw SQL.
SELECT 1;
