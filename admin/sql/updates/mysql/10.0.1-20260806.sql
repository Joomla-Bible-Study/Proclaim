SET @dbname = DATABASE();
SET @tablename = '#__bsms_podcast';
SET @columnname = 'podcastlink';

SET @stmt = (
    SELECT IF(
                   COUNT(*) = 0,
                   CONCAT('ALTER TABLE ', @tablename,
                          ' ADD COLUMN ', @columnname, ' VARCHAR(100) DEFAULT NULL'),
                   'SELECT "Column already exists"'
           )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
);

PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;