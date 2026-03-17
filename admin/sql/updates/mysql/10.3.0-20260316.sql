-- Migrate landing_ordering data into ordering column, then drop landing_ordering
UPDATE `#__bsms_teachers`
SET `ordering` = `landing_ordering`
WHERE `landing_ordering` > 0 AND (`ordering` = 0 OR `ordering` = '' OR `ordering` IS NULL);

-- Set alphabetical ordering for any teachers that still have no ordering value
SET @row_number = 0;
UPDATE `#__bsms_teachers` AS t
    INNER JOIN (
        SELECT `id`, (@row_number := @row_number + 1) AS new_ordering
        FROM `#__bsms_teachers`
        WHERE `ordering` = 0 OR `ordering` = '' OR `ordering` IS NULL
        ORDER BY `teachername` ASC
    ) AS ranked ON t.`id` = ranked.`id`
SET t.`ordering` = ranked.`new_ordering`;

ALTER TABLE `#__bsms_teachers` DROP COLUMN IF EXISTS `landing_ordering`;
